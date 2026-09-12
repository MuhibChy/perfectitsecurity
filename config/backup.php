<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup system master switch
    |--------------------------------------------------------------------------
    */
    'enabled' => env('BACKUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Schedules (cron expressions, evaluated by the scheduler; changing these
    | never requires editing application code — see Admin Backup Center)
    |--------------------------------------------------------------------------
    */
    'schedules' => [
        'full' => env('BACKUP_SCHEDULE_FULL', '0 2 * * *'),      // daily 02:00
        'db' => env('BACKUP_SCHEDULE_DB', '0 */6 * * *'),        // every 6h
        'files' => env('BACKUP_SCHEDULE_FILES', '0 2 * * *'),    // daily 02:00
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention policy (configurable from admin; backups under legal/
    | financial hold or the sole remaining backup are never deleted)
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'keep_daily' => (int) env('BACKUP_KEEP_DAILY', 7),
        'keep_weekly' => (int) env('BACKUP_KEEP_WEEKLY', 4),
        'keep_monthly' => (int) env('BACKUP_KEEP_MONTHLY', 12),
        // Pre-deployment backups are kept until manually reviewed.
        'keep_pre_deploy' => env('BACKUP_KEEP_PRE_DEPLOY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    | Local private disk is always the first destination. The S3 adapter is
    | only engaged when BACKUP_S3_ENABLED=true and credentials are present;
    | a backup is NOT marked successful if the external upload fails.
    |--------------------------------------------------------------------------
    */
    'disk' => env('BACKUP_DISK', 'local'),
    'directory' => env('BACKUP_DIRECTORY', 'backups'),
    's3' => [
        'enabled' => env('BACKUP_S3_ENABLED', false),
        'bucket' => env('BACKUP_S3_BUCKET'),
        'region' => env('BACKUP_S3_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        'prefix' => env('BACKUP_S3_PREFIX', 'techsupport-backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption (dedicated keys — never reuse APP_KEY; keys never stored
    | alongside archives or in backup metadata)
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', true),
        'cipher' => env('BACKUP_ENCRYPTION_CIPHER', 'AES-256-CBC'),
        // base64-encoded 32-byte key. Generate: php -r "echo base64_encode(random_bytes(32));"
        'key' => env('BACKUP_ENCRYPTION_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File backup scope (allowlist under storage/app; private visibility
    | is preserved on restore)
    |--------------------------------------------------------------------------
    */
    'file_sources' => [
        'ticket-attachments',
        'expense-receipts',
        'public/blog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety limits
    |--------------------------------------------------------------------------
    */
    'max_bytes' => (int) env('BACKUP_MAX_BYTES', 2 * 1024 * 1024 * 1024), // 2 GiB guard
    'timeout_seconds' => (int) env('BACKUP_TIMEOUT_SECONDS', 1800),
    'retries' => (int) env('BACKUP_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | Notifications (admin dashboard + mail; never include customer data)
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'mail_to' => env('BACKUP_NOTIFY_EMAIL'),
        'on_success' => env('BACKUP_NOTIFY_ON_SUCCESS', false),
        'on_failure' => env('BACKUP_NOTIFY_ON_FAILURE', true),
    ],

];
