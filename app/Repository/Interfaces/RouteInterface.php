<?php

namespace App\Repository\Interfaces;

use Illuminate\Http\Request;

interface RouteInterface{
    public function index($page,$search);
    
}