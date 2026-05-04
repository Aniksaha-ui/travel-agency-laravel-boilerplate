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
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse("An error occurred while fetching blogs");
        }
    }

    /**
     * Get single blog.
     */
    public function show($id)
    {
        try {
            $response = $this->blogService->getById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse("An error occurred while fetching blog");
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
                return $this->failedResponse("Validation Error", 422, [
                    "errors" => $validator->errors()
                ]);
            }

            $response = $this->blogService->create($request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 201));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse("An error occurred while creating blog");
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
                return $this->failedResponse("Validation Error", 422, [
                    "errors" => $validator->errors()
                ]);
            }

            $response = $this->blogService->update($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse("An error occurred while updating blog");
        }
    }

    /**
     * Delete blog.
     */
    public function destroy($id)
    {
        try {
            $response = $this->blogService->delete($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse("An error occurred while deleting blog");
        }
    }
}
