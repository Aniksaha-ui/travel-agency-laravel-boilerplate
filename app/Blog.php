<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'author',
        'publishDate',
        'status',
        'coverImage',
        'metaDescription',
        'content',
        'components_json',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'publishDate' => 'date',
    ];
}
