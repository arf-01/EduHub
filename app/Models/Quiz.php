<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Quiz extends Model
{
    //
    use HasFactory;

    // Specify the table if it's not the plural of the model name
    protected $table = 'quizzes'; // Example: 'my_quizzes'

    // Specify the columns you want to allow mass assignment
    /**
     * NOTE: `duration` stores the total quiz duration in SECONDS
     * (sum of per-question durations set by the teacher).
     * Do NOT treat this value as minutes.
     */
    protected $fillable = ['title', 'description', 'start_datetime', 'duration', 'userid'];
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function teacher()
{
    return $this->belongsTo(User::class, 'userid');
}

}
