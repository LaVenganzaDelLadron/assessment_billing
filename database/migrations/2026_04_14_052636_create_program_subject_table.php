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
        Schema::create('program_subject', function (Blueprint $table) {
            $table->string('subject_id');
            $table->string('program_id');
            $table->unsignedSmallInteger('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->string('school_year')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(['subject_id', 'program_id'], 'program_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_subject');
    }
};
