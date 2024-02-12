<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function getCategory(){
        try {
            $categories = Category::all();
            $resData = responseHelper::response(200, "Category berhasil dimuat");
            return CategoryResource::collection($categories)->additional($resData);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server. silahkan coba lagi');
            return response()->json($resData, 500);
        }
    }
}
