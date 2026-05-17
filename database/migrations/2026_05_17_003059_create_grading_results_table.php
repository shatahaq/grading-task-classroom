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
        Schema::create('grading_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('student_id');
            $table->decimal('score_ai', 6, 2)->nullable();
            $table->enum('confidence', ['LOW', 'MEDIUM', 'HIGH'])->default('MEDIUM');
            $table->enum('status', ['APPROVED', 'REVIEW', 'FAILED'])->default('REVIEW');
            $table->text('reason')->nullable();
            $table->longText('feedback_email')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_results');
    }
};
