<?php

namespace App\Http\Controllers;

use App\Helper\responseHelper;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT as JWTAuthJWT;

class UserController extends Controller
{
    public function register(Request $request){
        try {
            $requestData = $request->validate([
                'username' => 'required|max:100|unique:users',
                'fullname' => 'required|max:255',
                'password' => 'required|min:6|max:50',
                'retypePassword' => 'required|same:password'
            ]);

            $user = User::create([
                'username' => $requestData['username'],
                'fullname' => $requestData['fullname'],
                'password' => bcrypt($requestData['password']),
                'role' => 'User',
                'is_deleted' => false,
            ]);

            event(new Registered($user));

            $resData = responseHelper::response(200, 'User '.$requestData['username'].'Berhasil mendaftar');
            return response()->json($resData);
        }catch(\Illuminate\Validation\ValidationException $error){
            $errors = $error->validator->errors()->all();

            if(in_array('The username has already been taken.', $errors)){
                $resData = responseHelper::response(422, "Username telah digunakan oleh user yang telah mendaftar sebelumnya.");
                return response()->json($resData, 422);
            }
            if(in_array('The password must be at least 6 characters.', $errors)){
                $resData = responseHelper::response(422, "Kata sandi tidak boleh klurang dari 6 karakter.");
                return response()->json($resData, 422);
            }
            if(in_array('The retype password and password must match.', $errors)){
                $resData = responseHelper::response(422, "Konfimasi kata sandi tidak sama dengan kata sandi.");
                return response()->json($resData, 422);
            }
            $resData = responseHelper::response(422, "Validation Failed");
            return response()->json($resData, 422);
        }catch (\Exception $error) {
            Log::error($error->getMessage());
            $resData = responseHelper::response(500, 'Terjadi kesalahan server. Silahkan coba kembali');
            return response()->json($resData);
        }
    }

    public function login(LoginRequest $request){
        $credentials = $request->only('username', 'password');

        if(!$token = JWTAuth::attempt($credentials)){
            $resData = responseHelper::response(401, 'Unathorized');
            return response()->json($resData);
        }

        $user = JWTAuth::user();
        $resData = responseHelper::response(200, 'Login Berhasil', 1, [
            'id' => $user->user_id,
            'token' => $token,
            'type' => 'Bearer',
            'username' => $user->username,
            'role' => $user->role,
        ]);
        return response()->json($resData)->header('Authorization', 'Bearer ' . $token);
    }
}
