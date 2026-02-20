<?php

namespace App\Http\Controllers\User\Blog;

use App\Http\Controllers\Controller;
use App\Repository\Services\Blog\BlogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    private $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    /**
     * List all published blogs.
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $search = $request->query('search');

            $response = $this->blogService->getPublished($page, $search);

            return response()->json($response, 200);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "isExecute" => "FAILED",
                "message" => "An error occurred while fetching blogs"
            ], 500);
        }
    }

    /**
     * Get single blog by slug.
     */
    public function show($id)
    {
        try {
            $response = $this->blogService->getBySlug($id);

            $status = $response['isExecute'] === "SUCCESS" ? 200 : 404;
            return response()->json($response, $status);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "isExecute" => "FAILED",
                "message" => "An error occurred while fetching blog"
            ], 500);
        }
    }
}
