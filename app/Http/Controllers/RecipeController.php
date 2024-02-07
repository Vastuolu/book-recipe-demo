<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Resources\DetailRecipeResource;
use App\Http\Resources\RecipeResource;
use App\Models\FavoriteFood;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecipeController extends Controller
{
    public function getRecipe(Request $request){
        try {
            $recipes = Recipe::with('user', 'category', 'level')->where('is_deleted', false);
            $user_id = $request->input('user_id');

            if(!is_null($user_id)){
                $userFavFoods = FavoriteFood::where('user_id', $user_id)->get();
            }else{
                $userFavFoods = collect();
            }

            $time = $request->input('time');
            switch($time){
                case '0-30':
                    $recipes->whereBetween('time_cook', [0,30]);
                    break;
                case '30-60':
                    $recipes->whereBetween('time_cook', [30,60]);
                    break;
                case 60:
                    $recipes->where('time_cook', '>', 60);
                    break;
                default :
                    $recipes->where('time_cook', '>', 0);
            }

            $recipes = RecipeController::commonFunction($recipes,$request);

            $pageSize = intval($request->input('pageSize'));
            $pageNumber = intval($request->input('pageNumber'));
            $dataRecipes = $recipes->paginate($pageSize, ['*'], 'page', $pageNumber);
            $total = $dataRecipes->total();

            $dataRecipes->getCollection()->transform(function ($recipe) use ($userFavFoods){
                $recipe->user_fav_foods = $userFavFoods ? $userFavFoods->where('recipe_id', $recipe->recipe_id)->toArray() : [];
                return $recipe;
            });

            if($total === 0){
                $resData = responseHelper::response(200, 'Resep masakan tidak tersedia',0, []);
                return response()->json($resData);
            }

            $resData = responseHelper::response(200, 'Data Berhasil Dimuat', $total);
            return RecipeResource::collection($dataRecipes)->additional($resData);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server, silahkan coba lagi');
            return response()->json($resData, 500);
        }
    }

    public function detailRecipe(Request $request, $recipe_id){
        try {
            $recipe = Recipe::with('user', 'level', 'category')->find($recipe_id);
            $total = $recipe->count();
            $user_id = JWTAuth::user()->user_id ?? $request->input('UserId');
            $recipe->isFavorite = FavoriteFood::where('user_id', $user_id)->where('recipe_id', $recipe_id)->exists();

            if($total === 0){
                $resData = responseHelper::response(404, 'Recipe tidak ditemukan', $total);
                return response()->json($resData);
            }

            $resData = responseHelper::response(200, 'Detail Recipe berhasil dimuat', $total);
            return (new DetailRecipeResource($recipe))->additional($resData);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server, silahkan coba lagi.');
            return response()->json($resData,500);
        }
    }

    public static function commonFunction($query, $request){
        try {
            $recipeName = $request->input('recipeName');
            $levelId = $request->input('levelId');
            $categoryId = $request->input('categoryId');
            $sortBy = $request->input('sortBy');

            if(!is_null($recipeName)){
                $query->whereRaw('LOWER(recipe_name) LIKE ?', ['%'. strtolower($recipeName) . '%'] );
            }
            if(!is_null($levelId)){
                $query->where('level_id', $levelId);
            }
            if(!is_null($categoryId)){
                $query->where('category_id', $categoryId);
            }

            switch($sortBy){
                case 'recipeName,asc':
                    return $query = $query->orderByRaw("LOWER(REPLACE(recipe_name, ' ', '')) ASC, recipe_name");
                    break;
                case 'recipeName,desc':
                    return $query = $query->orderByRaw("LOWER(REPLACE(recipe_name, ' ', '')) DESC, recipe_name");
                    break;
                case 'timeCook,asc':
                    return $query = $query->orderBy('time_cook');
                    break;
                case 'timeCook,desc':
                    return $query = $query->orderByDesc('time_cook');
                    break;
                default:
                   return $query = $query->orderByRaw("LOWER(REPLACE(recipe_name, ' ', '')) ASC, recipe_name");
            }
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            return $error;
        }
    }
}
