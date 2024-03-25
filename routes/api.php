<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::prefix('v1')->group(function () {
    Route::controller(\App\Http\Controllers\AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::get('login', 'redirectlogin')->name('login');
        Route::post('register', 'register');
        Route::post('logout', 'logout')->middleware(['auth:sanctum']);
    });
    Route::controller(\App\Http\Controllers\FormController::class)->group(function () {
        Route::post('forms', 'create')->middleware('auth:sanctum');
        Route::get('forms', 'getAll')->middleware('auth:sanctum');
        Route::get('forms/{formslug}', 'getDetail')->middleware('auth:sanctum');
    });
    Route::controller(\App\Http\Controllers\QuestionController::class)->group(function () {
        Route::middleware('auth:sanctum')->post('forms/{form_slug}/questions', 'addQuest');
        Route::middleware('auth:sanctum')->delete('forms/{form_slug}/questions/{question_id}', 'removeQuest');
    });
    Route::controller(\App\Http\Controllers\ResponseController::class)->group(function () {
        Route::middleware('auth:sanctum')->post('forms/{form_slug}/responses', 'submit');
        Route::middleware('auth:sanctum')->get('forms/{form_slug}/responses', 'getAll');
    });
});
