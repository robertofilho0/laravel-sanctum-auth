<?php

use App\Http\Controllers\Api\AlunoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotaController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::apiResource('users', UserController::class)->only(['index', 'store', 'destroy']);

    Route::apiResource('alunos', AlunoController::class);
    Route::get('/alunos/{aluno}/boletim', [AlunoController::class, 'boletim']);

    Route::apiResource('alunos.notas', NotaController::class)->shallow()->only(['index', 'store', 'update', 'destroy']);

    Route::get('/relatorios/turma', [RelatorioController::class, 'turma']);
});
