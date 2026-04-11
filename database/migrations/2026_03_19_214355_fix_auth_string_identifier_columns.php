<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sessions MODIFY user_id VARCHAR(255) NULL');
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id VARCHAR(255) NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE VARCHAR(255) USING user_id::varchar');
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id DROP NOT NULL');
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE VARCHAR(255) USING tokenable_id::varchar');
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id SET NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id BIGINT UNSIGNED NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE sessions ALTER COLUMN user_id TYPE BIGINT USING CASE WHEN user_id ~ '^[0-9]+$' THEN user_id::bigint ELSE NULL END");
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id DROP NOT NULL');
            DB::statement("ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE BIGINT USING CASE WHEN tokenable_id ~ '^[0-9]+$' THEN tokenable_id::bigint ELSE 0 END");
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id SET NOT NULL');
        }
    }
};
