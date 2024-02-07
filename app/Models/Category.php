<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable =[
        'category_id',
        'category_name',
        'created_time',
        'created_by',
        'modified_time',
        'modified_by',
    ];

    public function recipes(){
        return $this->hasMany(Recipe::class);
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
