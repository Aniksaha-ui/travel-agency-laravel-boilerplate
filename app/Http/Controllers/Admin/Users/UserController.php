<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Repository\Services\Users\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->userService->index($page, $search);
            return $this->successResponse($response);
        } catch (\Exception $ex) {
            Log::error("UserController index: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
