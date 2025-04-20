<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Egg extends Model
{
    use Sluggable;

    protected $table = 'eggs';

    protected $primaryKey = 'origin_id';

    protected $fillable = [
        'origin_id',
        'uuid',
        'name',
        'description',
        'docker_images',
        'url',
        'variables',
        'startup',
        'public',
    ];

    protected $casts = [
        'docker_images' => 'array',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'name'],
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
