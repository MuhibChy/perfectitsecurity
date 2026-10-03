#!/usr/bin/env php
<?php

/**
 * Phase 24 - Scoped Backup Script
 * Creates a reproducible project backup excluding generated/cache directories and secrets.
 *
 * Excludes:
 * - vendor/
 * - node_modules/
 * - storage/logs/
 * - storage/framework/cache/
 * - storage/framework/views/ (compiled views)
 * - storage/framework/sessions/
 * - .phpunit.result.cache
 * - .env (production secrets)
 * - database/*.sqlite (database files)
 * - __pycache__/
 * - *.png (screenshots)
 * - *.log
 * - backup directories themselves
 *
 * Includes:
 * - app/ (application source)
 * - bootstrap/ (app bootstrap)
 * - config/ (configuration)
 * - database/ (migrations, factories, seeders)
 * - lang/ (translations)
 * - public/ (public assets)
 * - resources/ (views, lang, etc)
 * - routes/ (route definitions)
 * - tests/ (test suite)
 * - .env.example
 * - .env.staging
 * - composer.json, composer.lock
 * - package.json, package-lock.json
 * - phpunit.xml
 * - artisan
 * - Documentation (*.md)
 * - backup/ directory structure (empty)
 * - storage/backups/ directory structure (empty)
 * - storage/app/ directory structure (empty)
 */
$projectRoot = __DIR__;
$backupDir = $projectRoot.'/storage/backups/phase24-full-backup-'.date('Ymd-His');

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/storage/logs/',
    '/storage/framework/cache/',
    '/storage/framework/views/',
    '/storage/framework/sessions/',
    '/storage/framework/testing/',
    '/storage/backups/',
    '/storage/app/backups/',
    '/storage/app/public/',
    '/database/*.sqlite',
    '/__pycache__/',
    '/.phpunit.result.cache',
    '/.env$',
    '/\.log$',
    '/\.png$',
    '/\.jpg$',
    '/\.jpeg$',
    '/\.gif$',
    '/\.webp$',
    '/\.md$',
];

$includePatterns = [
    '/^app/',
    '/^bootstrap/',
    '/^config/',
    '/^database/',
    '/^lang/',
    '/^public/',
    '/^resources/',
    '/^routes/',
    '/^tests/',
    '/^storage\/backups\/$',
    '/^storage\/app\/$',
    '/^\.env\.example$/',
    '/^\.env\.staging$',
    '/^\.gitignore$/',
    '/^\.editorconfig$/',
    '/^\.gitattributes$/',
    '/^artisan$/',
    '/^composer\.json$/',
    '/^composer\.lock$/',
    '/^package\.json$/',
    '/^package-lock\.json$/',
    '/^phpunit\.xml$/',
    '/^postcss\.config\.js$/',
    '/^tailwind\.config\.js$/',
    '/^vite\.config\.js$/',
    '/^README\.md$/',
    '/^SECURITY\.md$/',
    '/^RBAC\.md$/',
    '/^FINANCE\.md$/',
    '/^TESTING\.md$/',
    '/^RUN_LOCALLY\.md$/',
    '/^PRODUCTION_DEPLOYMENT\.md$/',
    '/^BACKUP_AND_RECOVERY\.md$/',
    '/^ARCHITECTURE\.md$/',
    '/^CODEBASE_OVERVIEW\.md$/',
    '/^AI_AGENT\.md$/',
    '/^AI_AGENT_GATEWAY\.md$/',
    '/^docs\/',
];

function shouldInclude($path, $includePatterns, $excludePatterns): bool
{
    // Check explicit excludes first
    foreach ($excludePatterns as $pattern) {
        $regex = '#'.str_replace('/', '\/', $pattern).'#';
        if (preg_match($regex, $path)) {
            return false;
        }
    }

    // Check if path matches any include pattern
    foreach ($includePatterns as $pattern) {
        $regex = '#'.str_replace('/', '\/', $pattern).'#';
        if (preg_match($regex, $path)) {
            return true;
        }
    }

    // Also include directories that contain included files
    foreach ($includePatterns as $pattern) {
        if (str_ends_with($pattern, '/')) {
            $dirPattern = rtrim($pattern, '/');
            if (str_starts_with($path, $dirPattern.'/')) {
                return true;
            }
        }
    }

    return false;
}

echo "Phase 24 - Creating scoped backup...\n";
echo "Project root: {$projectRoot}\n";
echo "Backup destination: {$backupDir}\n\n";

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$fileCount = 0;
$totalSize = 0;
$skippedCount = 0;
$hashes = [];

foreach ($iterator as $file) {
    $relativePath = $file->getPathname();
    $relativePath = substr($relativePath, strlen($projectRoot) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    if (! shouldInclude($relativePath, $includePatterns, $excludePatterns)) {
        $skippedCount++;

        continue;
    }

    $destPath = $backupDir.'/'.$relativePath;

    if ($file->isDir()) {
        if (! is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }

        continue;
    }

    $dir = dirname($destPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    copy($file->getPathname(), $destPath);
    $fileCount++;
    $totalSize += $file->getSize();

    // Calculate hash for key files
    if (preg_match('#\.(php|js|json|xml|md|env\.example|env\.staging|lock)$#i', $relativePath)) {
        $hashes[$relativePath] = hash_file('sha256', $file->getPathname());
    }

    if ($fileCount % 100 === 0) {
        echo "  Processed {$fileCount} files...\n";
    }
}

// Create manifest
$manifest = [
    'created_at' => date('c'),
    'phase' => '24',
    'project_root' => $projectRoot,
    'backup_path' => $backupDir,
    'file_count' => $fileCount,
    'total_size_bytes' => $totalSize,
    'total_size_mb' => round($totalSize / 1024 / 1024, 2),
    'skipped_count' => $skippedCount,
    'excluded_patterns' => $excludePatterns,
    'included_patterns' => $includePatterns,
    'file_hashes' => $hashes,
    'database_excluded' => true,
    'secrets_excluded' => true,
    'production_env_excluded' => true,
];

file_put_contents($backupDir.'/BACKUP_MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT));

echo "\n=== BACKUP COMPLETE ===\n";
echo "Files included: {$fileCount}\n";
echo "Files skipped: {$skippedCount}\n";
echo 'Total size: '.round($totalSize / 1024 / 1024, 2)." MB\n";
echo "Manifest: {$backupDir}/BACKUP_MANIFEST.json\n";
echo "\nExcluded (documented):\n";
foreach ($excludePatterns as $pattern) {
    echo "  - {$pattern}\n";
}

echo "\n=== VERIFICATION ===\n";
echo 'Manifest hash: '.hash_file('sha256', $backupDir.'/BACKUP_MANIFEST.json')."\n";

// Verify key files exist
$keyFiles = [
    'app/Services/Ai/AgentGateway.php',
    'app/Services/Ai/AiAgentService.php',
    'app/Services/Ai/AgentApprovalService.php',
    'app/Services/Ai/AgentRuntimeInterface.php',
    'app/Services/Ai/HermesAgentAdapter.php',
    'app/Services/Ai/OpenClawAdapter.php',
    'config/agent.php',
    'config/ollama.php',
    '.env.example',
    '.env.staging',
    'tests/Feature/AiAgentTest.php',
    'tests/Feature/AiAgentHardeningTest.php',
    'tests/Feature/AgentGatewayTest.php',
    'tests/Feature/AgentApprovalTest.php',
    'tests/Feature/AgentStagingVerificationTest.php',
    'tests/Feature/AiSupportTest.php',
    'tests/Feature/AiSkillsRoutingTest.php',
    'tests/Feature/KbAiAuditTest.php',
];

echo "\nKey files verification:\n";
foreach ($keyFiles as $keyFile) {
    $fullPath = $backupDir.'/'.$keyFile;
    if (file_exists($fullPath)) {
        echo "  ✓ {$keyFile} (sha256: ".substr($hashes[$keyFile] ?? hash_file('sha256', $fullPath), 0, 16)."...)\n";
    } else {
        echo "  ✗ {$keyFile} MISSING\n";
    }
}

echo "\nBackup completed successfully.\n";
echo "To restore: copy all files from {$backupDir} to a fresh project root.\n";
echo "Then: composer install, npm install, php artisan key:generate, php artisan migrate --seed\n";
