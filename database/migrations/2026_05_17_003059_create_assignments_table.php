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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('course_id');
            $table->string('coursework_id')->nullable();
            $table->string('title');
            $table->decimal('max_score', 6, 2)->default(100);
            $table->string('file_id')->nullable();
            $table->boolean('auto_approval')->default(false);
            $table->boolean('auto_email')->default(false);
            $table->enum('grade_mode', ['none', 'draft', 'final'])->default('draft');
            $table->enum('status', ['draft', 'ready', 'grading', 'completed', 'failed'])->default('draft');
            $table->timestamps();

            $table->index(['teacher_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
