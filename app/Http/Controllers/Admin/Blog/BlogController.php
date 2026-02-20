<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Repository\Services\Blog\BlogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    private $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    /**
     * List all blogs.
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $search = $request->query('search');

            $response = $this->blogService->getAll($page, $search);

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
     * Get single blog.
     */
    public function show($id)
    {
        try {
            $response = $this->blogService->getById($id);

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

    /**
     * Create blog.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:blogs,slug',
                'author' => 'nullable|string|max:255',
                'publishDate' => 'nullable|date',
                'status' => 'required|in:draft,published,archived',
                'coverImage' => 'nullable|string',
                'metaDescription' => 'nullable|string',
                'content' => 'nullable|string',
                'components_json' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "isExecute" => "FAILED",
                    "message" => "Validation Error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $response = $this->blogService->create($request->all());

            return response()->json($response, 201);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "isExecute" => "FAILED",
                "message" => "An error occurred while creating blog"
            ], 500);
        }
    }

    /**
     * Update blog.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:blogs,slug,' . $id,
                'author' => 'nullable|string|max:255',
                'publishDate' => 'nullable|date',
                'status' => 'required|in:draft,published,archived',
                'coverImage' => 'nullable|string',
                'metaDescription' => 'nullable|string',
                'content' => 'nullable|string',
                'components_json' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "isExecute" => "FAILED",
                    "message" => "Validation Error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $response = $this->blogService->update($id, $request->all());

            $status = $response['isExecute'] === "SUCCESS" ? 200 : 404;
            return response()->json($response, $status);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "isExecute" => "FAILED",
                "message" => "An error occurred while updating blog"
            ], 500);
        }
    }

    /**
     * Delete blog.
     */
    public function destroy($id)
    {
        try {
            $response = $this->blogService->delete($id);

            $status = $response['isExecute'] === "SUCCESS" ? 200 : 404;
            return response()->json($response, $status);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "isExecute" => "FAILED",
                "message" => "An error occurred while deleting blog"
            ], 500);
        }
    }
}
