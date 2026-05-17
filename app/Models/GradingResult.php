<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingResult extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'student_email',
        'score_ai',
        'confidence',
        'needs_review',
        'status',
        'extraction_status',
        'extracted_text_length',
        'output_json_valid',
        'reason',
        'feedback_email',
        'rubric_breakdown',
        'raw_llm_output',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'score_ai' => 'decimal:2',
            'needs_review' => 'boolean',
            'extracted_text_length' => 'integer',
            'output_json_valid' => 'boolean',
            'rubric_breakdown' => 'array',
            'raw_llm_output' => 'array',
            'email_sent' => 'boolean',
        ];
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
