<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    //
    protected $fillable = ['student_id', 'quiz_id', 'device_id', 'score'];
    public function details()
{
    return $this->hasMany(ResultDetail::class);
}


}

