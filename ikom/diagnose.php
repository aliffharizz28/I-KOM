<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== LOGIN DIAGNOSTIC ===\n\n";

// Check database connection
try {
    DB::connection()->getPdo();
    echo "✓ Database connection: OK\n";
} catch (\Exception $e) {
    echo "✗ Database connection: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    exit;
}

// Check if pengguna table exists
$tableExists = DB::getSchemaBuilder()->hasTable('pengguna');
echo $tableExists ? "✓ Pengguna table: EXISTS\n" : "✗ Pengguna table: NOT FOUND\n";

// Check users in database
$userCount = pengguna::count();
echo "✓ Users in database: $userCount\n";

if ($userCount > 0) {
    $users = pengguna::all();
    echo "\nUsers:\n";
    foreach ($users as $user) {
        echo "  - Email: " . $user->fld_user_email . "\n";
        echo "    ID: " . $user->fld_user_id . "\n";
        echo "    Role: " . $user->fld_user_role . "\n";
        echo "    Password hash length: " . strlen($user->fld_user_pass) . "\n";
    }
} else {
    echo "\n⚠ No users found in database!\n";
    echo "Create a test user with:\n";
    echo "INSERT INTO pengguna (fld_user_id, fld_user_nama, fld_user_email, fld_user_pass, fld_user_role, created_at, updated_at)\n";
    echo "VALUES ('001', 'Test User', 'test@example.com', '" . Hash::make('password123') . "', 'Pelajar', NOW(), NOW());\n";
}

// Check sessions table
$sessionTableExists = DB::getSchemaBuilder()->hasTable('sessions');
echo "\n" . ($sessionTableExists ? "✓ Sessions table: EXISTS\n" : "✗ Sessions table: NOT FOUND\n");

echo "\n=== END DIAGNOSTIC ===\n";
