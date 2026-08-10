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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('title');
            $table->unsignedBigInteger('userid'); // Foreign key
            $table->dateTime('start_datetime'); // Corrected start_datetime column
            $table->integer('duration'); // Assuming duration is an integer (e.g., seconds)
            $table->timestamps();
        
            // Foreign key constraint
            $table->foreign('userid')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
