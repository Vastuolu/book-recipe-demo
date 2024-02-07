<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing  = true;
    protected $keyType = 'integer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'password',
        'role',
        'is_deleted',
        'created_by',
        'created_time',
        'modified_by',
        'modified_time',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public $timestamps = false;

    public function recipes(){
        return $this->hasMany(Recipe::class);
    }

    public function favorite_food(){
        return $this->hasMany(FavoriteFood::class);
    }

    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [
            'user'=>[
                'id' => $this->user_id,
                'username' => $this->username,
                'role' => $this->role
            ]
        ];
    }

    public static function boot(){
        parent::boot();
        static::creating(function($model){
            $model->created_time = now();
        });
        static::updating(function($model){
            $model->modified_time = now();
        });
    }
}
