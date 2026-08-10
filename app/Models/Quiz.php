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
    protected $fillable = ['title', 'description','start_datetime','duration']; // Add the table's column names here
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function teacher()
{
    return $this->belongsTo(User::class, 'userid');
}

}
