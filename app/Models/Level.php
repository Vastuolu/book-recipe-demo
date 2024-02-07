<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';
    protected $primaryKey = 'level_id';
    public $timestamps = false;

    protected $fillable =[
        'level_id',
        'level_name',
        'created_by',
        'created_time',
        'modified_by',
        'modified_time',
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
