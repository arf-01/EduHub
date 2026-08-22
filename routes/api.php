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

// Lightweight network heartbeat check
Route::match(['get', 'head'], '/ping', function () {
    return response()->json(['ok' => true]);
});
