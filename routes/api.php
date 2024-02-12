<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteFoodController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use App\Models\Recipe;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user-management/users/sign-up', [UserController::class, 'register']);
Route::post('/user-management/users/signin', [UserController::class, 'login']);
Route::get('/book-recipe/book-recipes', [RecipeController::class, 'getRecipe']);
Route::get('/book-recipe/book-recipes/{recipe_id}', [RecipeController::class, 'detailRecipe']);
Route::get('/book-recipe/my-recipes', [RecipeController::class, 'myRecipes']);
Route::get('/book-recipe/my-favorite-recipes', [FavoriteFoodController::class, 'getFavorite']);
Route::put('/book-recipe/book-recipes/{recipeId}/favorites', [FavoriteFoodController::class, 'putFavorite']);
Route::post('/book-recipe/book-recipes', [RecipeController::class, 'addRecipe']);
Route::post('/book-recipe/book-recipes/edit', [RecipeController::class, 'updateRecipe']);
Route::put('/book-recipe/book-recipes/{recipe_id}', [RecipeController::class, 'softDelete']);
Route::get('/book-recipe-masters/category-option-lists', [CategoryController::class, 'getCategory']);
Route::get('/book-recipe-masters/level-option-lists', [LevelController::class, 'getLevel']);

