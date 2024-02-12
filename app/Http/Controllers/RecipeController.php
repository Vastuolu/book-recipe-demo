<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Resources\DetailRecipeResource;
use App\Http\Resources\RecipeResource;
use App\Models\FavoriteFood;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    public function getRecipe(Request $request){
        try {
            $recipes = Recipe::with('user', 'category', 'level')->where('is_deleted', false);
            $user_id = $request->input('userId');

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

    public function myRecipes(Request $request){
        try {
            $user_id = $request->input('userId');
            $recipes = Recipe::with('user', 'category', 'level')->where('is_deleted', false)->where('user_id', $user_id);

            if(!is_null($user_id)){
                $userFavFoods = FavoriteFood::where('user_id', $user_id)->get();
            }else{
                $userFavFoods = collect();
            }

            $time = $request->input('time');
            switch($time){
                case 30:
                    $recipes->whereBetween('time_cook', [0,30]);
                    break;
                case 60:
                    $recipes->whereBetween('time_cook', [30,60]);
                    break;
                case 90:
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
            return response()->json($resData,500);
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

    public function addRecipe(Request $request){
        try {
            $validated = $request->validate([
                'request' => 'required',
                'file' => 'required|max:1024',
            ]);

            $rules = [
                'userId' => 'required|exists:users,user_id',
                'categories.categoryId' => 'required|exists:categories,category_id',
                'categories.categoryName' => 'required|string',
                'levels.levelId' => 'required|exists:levels,level_id',
                'levels.levelName' => 'required|string',
                'recipeName' => 'required|string|max:255',
                'timeCook' => 'required|integer',
                'ingridient' => 'required',
                'howToCook' => 'required'
            ];

            if($request->hasFile('request')){
                $requestFile = $request->file('request');
                $requestData = file_get_contents($requestFile->getRealPath());
                $dataArray = json_decode($requestData, true);
            }

            if(isset($dataArray['userId'])){
                $user = User::find($dataArray['userId']);
                if(!$user){
                    $resData = responseHelper::response(404, 'User tidak ditemukan');
                    return response()->json($resData, 404);
                }
            }

            $validator = Validator::make($dataArray, $rules);

            if($validator->fails()){
                $resData = responseHelper::response(400, 'Terdapat Field Kosong');
                return response()->json($resData, 400);
            }

            $recipeName = $dataArray['recipeName'];
            $categoryName = $dataArray['categories']['categoryName'];
            $levelName = $dataArray['levels']['levelName'];
            $timestamps = now()->format('Ymd_His');

            $data = [
                'user_id' => $user->user_id,
                'category_id' => $dataArray['categories']['categoryId'],
                'level_id' => $dataArray['levels']['levelId'],
                'recipe_name' => $recipeName,
                'time_cook' => (int)$dataArray['timeCook'],
                'ingridient' => $dataArray['ingridient'],
                'how_to_cook' => $dataArray['howToCook'],
                'is_deleted' => false,
                'created_by' => $user->fullname
            ];

            if($request->hasFile('file')){
                $file = $request->file('file');
                $originalExtension = $file->getClientOriginalExtension();
                $supportedExtension = ['jpg', 'jpeg', 'png'];

                if(in_array($originalExtension, $supportedExtension)){
                    $recipeName = str_replace(' ', '_', $recipeName);

                    $newFileName = "{$recipeName}_{$categoryName}_{$levelName}_{$timestamps}.{$originalExtension}";

                    Storage::put($newFileName, file_get_contents($file->path()));

                    Storage::visibility($newFileName, 'public');

                    $data['image_filename'] = $newFileName;
                }else{
                    return response()->json(['error'=>'Invalid file Type']);
                }

            }

            Recipe::create($data);
            $resData = responseHelper::response(200, 'Recipe '. $recipeName . ' Berhasil ditambahkan');
            return response()->json($resData, 200);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, "Terjadi Kesalahan server silahkan coba lagi.");
            return response()->json($resData);
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
