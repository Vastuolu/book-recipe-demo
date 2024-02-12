<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LevelController extends Controller
{
    public function getLevel(){
        try {
            $levels = Level::all();
            $resData = responseHelper::response(200, "Leve berhasil dimuat");
            return LevelResource::collection($levels)->additional($resData);
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server. silahkan coba lagi');
            return response()->json($resData, 500);
        }
    }
}
