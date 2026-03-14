<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Api\ResultController;
use Illuminate\Support\Facades\Route;

// Аутентификация
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Публичные маршруты (опросы)
Route::get('/surveys', [SurveyController::class, 'index']);
Route::get('/surveys/{id}', [SurveyController::class, 'show']);

// Защищённые маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Опросы
    Route::post('/surveys', [SurveyController::class, 'store']);
    Route::put('/surveys/{id}', [SurveyController::class, 'update']);
    Route::post('/surveys/{id}/publish', [SurveyController::class, 'publish']);
    Route::post('/surveys/{id}/close', [SurveyController::class, 'close']);
    Route::delete('/surveys/{id}', [SurveyController::class, 'destroy']);

    // Вопросы
    Route::post('/surveys/{id}/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    // Варианты ответов
    Route::post('/questions/{id}/options', [OptionController::class, 'store']);
    Route::put('/options/{id}', [OptionController::class, 'update']);
    Route::delete('/options/{id}', [OptionController::class, 'destroy']);

    // Прохождение опросов
    Route::post('/surveys/{id}/respond', [ResponseController::class, 'store']);

    // Аналитика
    Route::get('/surveys/{id}/results', [ResultController::class, 'show']);
    Route::get('/surveys/{id}/results/export', [ResultController::class, 'export']);
});
