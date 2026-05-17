<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_id',
        'coursework_id',
        'title',
        'description',
        'max_score',
        'file_id',
        'classroom_link',
        'drive_folder_id',
        'question_drive_file_id',
        'rubric_drive_file_id',
        'answer_key_drive_file_id',
        'question_local_path',
        'rubric_local_path',
        'answer_key_local_path',
        'auto_approval',
        'auto_email',
        'grade_mode',
        'min_answer_length',
        'due_date',
        'close_on_due',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'auto_approval' => 'boolean',
            'auto_email' => 'boolean',
            'min_answer_length' => 'integer',
            'due_date' => 'datetime',
            'close_on_due' => 'boolean',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function gradingResults()
    {
        return $this->hasMany(GradingResult::class);
    }
}
