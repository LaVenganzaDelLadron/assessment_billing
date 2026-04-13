<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admission_user_id')->nullable()->after('remember_token');
            $table->string('admission_role')->nullable()->after('admission_user_id');
            $table->json('admission_profile')->nullable()->after('admission_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'admission_user_id',
                'admission_role',
                'admission_profile',
            ]);
        });
    }
};
