<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{

    public function dashboard(){
        $users = DB::table('users')->get();
        return response()->json([
            "data"=> $users,
            "message"=> "success"
        ],200);
    }
}
