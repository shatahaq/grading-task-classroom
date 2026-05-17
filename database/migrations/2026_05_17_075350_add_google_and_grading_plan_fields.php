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
        Schema::table('assignments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('classroom_link')->nullable()->after('coursework_id');
            $table->string('drive_folder_id')->nullable()->after('file_id');
            $table->string('question_drive_file_id')->nullable()->after('drive_folder_id');
            $table->string('rubric_drive_file_id')->nullable()->after('question_drive_file_id');
            $table->string('answer_key_drive_file_id')->nullable()->after('rubric_drive_file_id');
            $table->string('question_local_path')->nullable()->after('answer_key_drive_file_id');
            $table->string('rubric_local_path')->nullable()->after('question_local_path');
            $table->string('answer_key_local_path')->nullable()->after('rubric_local_path');
            $table->unsignedInteger('min_answer_length')->default(120)->after('grade_mode');
        });

        Schema::table('grading_results', function (Blueprint $table) {
            $table->string('student_email')->nullable()->after('student_id');
            $table->boolean('needs_review')->default(true)->after('confidence');
            $table->enum('extraction_status', ['PENDING', 'SUCCESS', 'PARTIAL', 'FAILED'])->default('PENDING')->after('status');
            $table->unsignedInteger('extracted_text_length')->default(0)->after('extraction_status');
            $table->boolean('output_json_valid')->default(false)->after('extracted_text_length');
            $table->json('rubric_breakdown')->nullable()->after('feedback_email');
            $table->json('raw_llm_output')->nullable()->after('rubric_breakdown');
        });

        Schema::table('oauth_tokens', function (Blueprint $table) {
            $table->string('token_type')->nullable()->after('scopes');
            $table->timestamp('last_refreshed_at')->nullable()->after('token_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_tokens', function (Blueprint $table) {
            $table->dropColumn(['token_type', 'last_refreshed_at']);
        });

        Schema::table('grading_results', function (Blueprint $table) {
            $table->dropColumn([
                'student_email',
                'needs_review',
                'extraction_status',
                'extracted_text_length',
                'output_json_valid',
                'rubric_breakdown',
                'raw_llm_output',
            ]);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'classroom_link',
                'drive_folder_id',
                'question_drive_file_id',
                'rubric_drive_file_id',
                'answer_key_drive_file_id',
                'question_local_path',
                'rubric_local_path',
                'answer_key_local_path',
                'min_answer_length',
            ]);
        });
    }
};
