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
        Schema::table('users', function (Blueprint $table) {
            $table->unique('room_name');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index('userid');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index('quiz_id');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->index('quiz_id');
            $table->index('student_id');
            $table->index(['quiz_id', 'score']);
        });

        Schema::table('result_details', function (Blueprint $table) {
            $table->index('result_id');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_details', function (Blueprint $table) {
            $table->dropIndex(['result_id']);
            $table->dropIndex(['question_id']);
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex(['quiz_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['quiz_id', 'score']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['quiz_id']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['userid']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['room_name']);
        });
    }
};
