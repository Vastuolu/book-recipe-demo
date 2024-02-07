<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'recipes';
    public $incrementing = true;
    protected $primaryKey = 'recipe_id';

    protected $fillable = [
        'user_id',
        'category_id',
        'level_id',
        'recipe_name',
        'image_filename',
        'time_cook',
        'ingridient',
        'how_to_cook',
        'is_deleted',
        'created_by',
        'created_time',
        'modified_by',
        'modified_time'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function category(){
        return $this->belongsTo(Category::class, 'category_id' , 'category_id');
    }

    public function level(){
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    public function favorite_food(){
        return $this->hasMany(FavoriteFood::class,);
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
