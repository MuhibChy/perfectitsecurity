<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\AuditService;
use App\Services\Backup\BackupOrchestratorService;
use App\Services\Backup\BackupRetentionService;
use App\Services\Backup\LocalBackupDiskAdapter;
use App\Services\Backup\RestoreService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index(BackupOrchestratorService $orchestrator)
    {
        $last = Backup::active()->latest()->first();
        $lastFailed = Backup::active()->where('status', 'failed')->latest()->first();
        $backups = Backup::active()->with('creator')->latest()->paginate(20);
        $storage = $orchestrator->storageStatus();
        $stats = [
            'total' => Backup::active()->count(),
            'verified' => Backup::active()->verified()->count(),
            'failed' => Backup::active()->where('status', 'failed')->count(),
            'bytes' => Backup::active()->verified()->sum('size_bytes'),
        ];
        $encryptionOk = config('backup.encryption.enabled', true) && ! empty(config('backup.encryption.key'));
        $schedules = config('backup.schedules');
        $retention = config('backup.retention');

        return view('admin.backups.index', compact(
            'last', 'lastFailed', 'backups', 'storage', 'stats', 'encryptionOk', 'schedules', 'retention'
        ));
    }

    public function show($id)
    {
        $backup = Backup::active()->with(['files', 'creator'])->findOrFail($id);

        return view('admin.backups.show', compact('backup'));
    }

    public function store(Request $request, BackupOrchestratorService $orchestrator)
    {
        $validated = $request->validate([
            'type' => 'required|in:full,db,files,pre_deploy',
            'confirm' => 'required|accepted',
        ]);
        try {
            $backup = $orchestrator->run($validated['type'], 'manual', auth()->user());

            return redirect()->route('admin.backups.show', $backup->id)
                ->with('success', "Backup {$backup->backup_id} verified successfully.");
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Backup failed: ' . $e->getMessage()]);
        }
    }

    public function verify($id)
    {
        $backup = Backup::active()->findOrFail($id);
        $workDir = storage_path('app/backups/tmp/verify-' . uniqid('', true));
        @mkdir($workDir, 0755, true);
        try {
            $detail = (new \App\Services\Backup\BackupVerificationService())
                ->verify($backup, new LocalBackupDiskAdapter(), $workDir);
            $backup->update([
                'verification_status' => 'passed', 'verified_at' => now(),
                'status' => 'verified', 'verification_detail' => json_encode($detail),
            ]);
            app(AuditService::class)->log('backup.verified', 'backups', $backup, "Backup {$backup->backup_id} manually verified.");

            return back()->with('success', 'Backup verification passed.');
        } catch (\Throwable $e) {
            $backup->update(['verification_status' => 'failed', 'verified_at' => now()]);

            return back()->withErrors(['backup' => 'Verification failed: ' . $e->getMessage()]);
        } finally {
            foreach (glob($workDir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($workDir);
        }
    }

    public function restoreTest($id, RestoreService $restore)
    {
        $backup = Backup::active()->findOrFail($id);
        try {
            $restore->restoreTest($backup);

            return back()->with('success', 'Isolated restore test passed.');
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Restore test failed: ' . $e->getMessage()]);
        }
    }

    public function restore(Request $request, $id, RestoreService $restore)
    {
        $backup = Backup::active()->findOrFail($id);
        $validated = $request->validate([
            'scope' => 'required|in:db,files,full',
            'confirm_token' => 'required|string',
            'acknowledge' => 'required|accepted',
        ]);
        try {
            $result = $restore->restore($backup, $validated['scope'], $validated['confirm_token'], auth()->user());

            return back()->with('success', "Restore complete (safety backup: {$result['detail']['safety_backup_id']}).");
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Restore failed: ' . $e->getMessage()]);
        }
    }

    public function download($id)
    {
        $backup = Backup::active()->findOrFail($id);
        abort_unless($backup->isVerified(), 422, 'Only verified backups may be downloaded.');
        $file = $backup->files()->orderByDesc('size_bytes')->firstOrFail();
        $tmp = tempnam(sys_get_temp_dir(), 'bkp-dl-');
        (new LocalBackupDiskAdapter())->get($file->path, $tmp);
        app(AuditService::class)->log('backup.downloaded', 'backups', $backup, "Backup {$backup->backup_id} ({$file->kind}) downloaded by " . auth()->user()->email . '.');

        return response()->download($tmp, $backup->backup_id . '-' . $file->kind . ($file->encrypted ? '.enc' : ''))->deleteFileAfterSend(true);
    }

    public function destroy($id)
    {
        $backup = Backup::active()->findOrFail($id);
        abort_if(Backup::active()->verified()->count() <= 1 && $backup->isVerified(), 422, 'Refusing to delete the only verified backup.');
        abort_if($backup->retention_hold, 422, 'This backup is under retention hold.');
        foreach ($backup->files as $file) {
            try {
                (new LocalBackupDiskAdapter())->delete($file->path);
            } catch (\Throwable) {
            }
        }
        $backup->update(['deleted_at' => now(), 'status' => 'deleted']);
        app(AuditService::class)->log('backup.deleted', 'backups', $backup, "Backup {$backup->backup_id} deleted by " . auth()->user()->email . '.');

        return redirect()->route('admin.backups.index')->with('success', 'Backup deleted (audit record retained).');
    }

    public function connectionTest(BackupOrchestratorService $orchestrator)
    {
        $status = $orchestrator->storageStatus();
        app(AuditService::class)->log('backup.connection_tested', 'backups', null, 'Backup storage connection test executed.');

        return back()->with('success', 'Local: ' . ($status['local']['ok'] ? 'OK' : $status['local']['detail'])
            . ' | S3: ' . (! $status['s3']['configured'] ? 'not configured' : ($status['s3']['ok'] ? 'OK' : $status['s3']['detail'])));
    }
}
