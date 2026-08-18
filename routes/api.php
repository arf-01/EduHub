<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Quiz Endpoints
Route::post('/quiz/room-quizzes', [\App\Http\Controllers\Api\QuizApiController::class, 'roomQuizzes']);
Route::post('/quiz/join', [\App\Http\Controllers\Api\QuizApiController::class, 'roomQuizzes']);
Route::post('/quiz/start', [\App\Http\Controllers\Api\QuizApiController::class, 'start']);
Route::post('/quiz/submit', [\App\Http\Controllers\Api\QuizApiController::class, 'submit']);


//Testing the load 

Route::get('/load-test', function () {
    usleep(1000000); // 1 second

    return response()->json([
        'message' => 'done',
    ]);
});
