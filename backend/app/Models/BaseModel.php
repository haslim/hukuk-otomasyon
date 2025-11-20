<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            }
        });
    }
}
