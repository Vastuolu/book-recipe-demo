<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Resources\FavoriteFoodResource;
use App\Models\FavoriteFood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteFoodController extends Controller
{
    public function getFavorite(Request $request){
        try {
            $user_id = $request->input('userId');
            $favorites = FavoriteFood::with('recipe.category', 'recipe.level', 'recipe')
            ->where('user_id', $user_id)
            ->where('is_favorite', true)
            ->whereHas('recipe', function ($query) use ($request){
                $query->where('is_deleted', false);
            });

            $favorites = FavoriteFoodController::commonFunction($favorites, $request);

            $pageSize = intval($request->input('pageSize'));
            $pageNumber = intval($request->input('pageNumber'));
            $dataFavorites = $favorites->paginate($pageSize, ['*'], 'page', $pageNumber);
            $total = $dataFavorites->total();

            $sortBy = $request->input('sortBy');
            $dataFavorites = FavoriteFoodController::sortBy($dataFavorites, $sortBy);

            if($total === 0){
                $resData = responseHelper::response(404, 'Tidak ada Resep Favorite',$total);
                return response()->json($resData, 404);
            }

            $resData = responseHelper::response(200, 'Data Favorite Resep berhasil dimuat',$total);
            return FavoriteFoodResource::collection($dataFavorites)->additional($resData);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server, silahkan coba lagi.');
            return response()->json($resData,500);
        }
    }

    public static function commonFunction($query, $request){
        try{
        $recipeName = $request->input('recipeName');
        $levelId = $request->input('levelId');
        $categoryId = $request->input('categoryId');
        $time = $request->input('time');

        $query->wherehas('recipe', function ($query) use ($recipeName, $levelId, $categoryId, $time){
            if(!is_null($recipeName)){
                $query->whereRaw('LOWER(recipe_name) LIKE ?', ['%'. strtolower($recipeName) . '%'] );
            }
            if(!is_null($levelId)){
                $query->where('level_id', $levelId);
            }
            if(!is_null($categoryId)){
                $query->where('category_id', $categoryId);
            }

            switch($time){
                case 30:
                    $query->whereBetween('time_cook', [0,30]);
                    break;
                case 60:
                    $query->whereBetween('time_cook', [30,60]);
                    break;
                case 90:
                    $query->where('time_cook', '>', 60);
                    break;
                default :
                    $query->where('time_cook', '>', 0);
            }
        });

        return $query;
    }catch(\Throwable $error){
        Log::error($error->getMessage());
        throw $error;
    }
    }

    public static function sortBy($collect, $sortBy){
        try {
            return $collect->sort(function($indexLeft, $indexRight) use ($sortBy){
                $recipeNameA = str_replace(' ', '', $indexLeft->recipe->recipe_name);
                $recipeNameB = str_replace(' ', '', $indexRight->recipe->recipe_name);
                $timeCookA = $indexLeft->recipe->time_cook;
                $timeCookB = $indexRight->recipe->time_cook;

                switch($sortBy){
                    case 'recipeName,asc':
                        return strcasecmp($recipeNameA, $recipeNameB);
                    case 'recipeName,desc':
                        return strcasecmp($recipeNameB, $recipeNameA);
                    case 'timeCook,asc':
                        return $timeCookA - $timeCookB;
                    case 'timeCook,desc':
                        return $timeCookB - $timeCookA;
                    default:
                        return strcasecmp($recipeNameA, $recipeNameB);
                }
            });
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            throw $error;
        }
    }
}
