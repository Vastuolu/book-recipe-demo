<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteFood extends Model
{
    use HasFactory;

    protected $table = 'favorite_foods';
    protected $primaryKey  = null;
    public $incrementing = false;
    protected $fillableprimarykey = null;
    public $timestamps = false;

    protected $fillable =[
        'user_id',
        'recipe_id',
        'is_favorite',
        'created_by',
        'created_time',
        'modified_by',
        'modified_time',
    ];

    public static function boot(){
        parent::boot();
        static::creating(function ($model){
            $model->created_time = now();
        });
        static::updating(function ($model){
            $model->modified_time = now();
        });
    }
}
