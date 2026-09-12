<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SELECT name FROM sqlite_master WHERE type="table" ORDER BY name');
echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "- " . $table->name . "\n";
}
