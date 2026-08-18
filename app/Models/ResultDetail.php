<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultDetail extends Model
{
    protected $fillable = [
        'result_id',
        'question_id',
        'selected_option',
        
    ];

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getIsCorrectAttribute()
    {
        if (!$this->selected_option || !$this->question) {
            return false;
        }
        return (int)$this->selected_option === (int)$this->question->right_option;
    }
}
