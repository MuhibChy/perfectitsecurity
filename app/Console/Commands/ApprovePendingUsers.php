<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ApprovePendingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:approve-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve all pending users with password "password"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Finding users...');

        // Show total users
        $totalUsers = User::count();
        $this->info("Total users: {$totalUsers}");

        // Show all users with their status
        $users = User::all(['id', 'name', 'email', 'role', 'is_active', 'approved_at']);
        
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Active', 'Approved At'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->is_active ? 'Yes' : 'No',
                    $user->approved_at ? $user->approved_at->format('Y-m-d H:i:s') : 'Never',
                ];
            })->toArray()
        );

        // Find all inactive users (not active)
        $pendingUsers = User::where('is_active', false)->get();

        if ($pendingUsers->isEmpty()) {
            $this->info('No inactive users found.');
            $this->info('All users are already active. Setting password "password" for all users...');
            
            // Set password to 'password' for all users
            foreach ($users as $user) {
                $user->password = Hash::make('password');
                $user->save();
                $this->info("✓ Password set for user: {$user->name}");
            }
            
            $this->info("Password 'password' set for all {$users->count()} users.");
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingUsers->count()} inactive users to approve.");

        if (!$this->confirm("Do you want to approve all {$pendingUsers->count()} inactive users with password 'password'?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        foreach ($pendingUsers as $user) {
            $this->info("Approving user: {$user->name} ({$user->email}) - Role: {$user->role}");

            // Set password to 'password'
            $user->password = Hash::make('password');

            // Approve the user
            $user->update([
                'is_active' => true,
                'approved_by' => 1, // Admin ID (assuming admin has ID 1)
                'approved_at' => now(),
                'approval_note' => 'Auto-approved via command with password "password"',
            ]);

            $this->info("✓ User {$user->name} approved with password 'password'");
        }

        $this->info("Successfully approved {$pendingUsers->count()} users.");
        return Command::SUCCESS;
    }
}
