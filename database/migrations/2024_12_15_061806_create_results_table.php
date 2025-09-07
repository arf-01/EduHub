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
        Schema::create('results', function (Blueprint $table) {
    $table->id();
    $table->string('student_id'); // Treat student_id as a string (or integer if numeric only)
    $table->unsignedBigInteger('quiz_id');
    $table->float('score', 8, 2)->default(0);  // Float with 2 decimal places
    $table->timestamps();

    // Only set foreign key for quiz_id
    $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
    $table->unique(['student_id', 'quiz_id']);
});
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};