<?php

namespace App;


use Illuminate\Database\Eloquent\Model;



class MenuItem extends Model
{


    protected $fillable = [
        'title',
        'path',
        'icon',
        'location',

        'parent_id',
        'order',
        'roles',
    ];

    protected $casts = [
        'roles' => 'array',
    ];


    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->with('children')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
}
