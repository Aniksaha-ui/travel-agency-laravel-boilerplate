<?php

namespace App\Repository\Services\Blog;

use App\Blog;
use Exception;
use Illuminate\Support\Facades\Log;

class BlogService
{
    /**
     * Get all blogs with pagination and search.
     */
    public function getAll($page, $search)
    {
        try {
            $perPage = 10;
            $query = Blog::query();

            if ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('author', 'like', '%' . $search . '%')
                      ->orWhere('slug', 'like', '%' . $search . '%');
            }

            $blogs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return [
                "isExecute" => "SUCCESS",
                "data" => $blogs
            ];

        } catch (Exception $ex) {
            Log::error("BlogService getAll error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error retrieving blogs"
            ];
        }
    }

    /**
     * Get a single blog by ID.
     */
    public function getById($id)
    {
        try {
            $blog = Blog::find($id);

            if ($blog) {
                return [
                    "isExecute" => "SUCCESS",
                    "data" => $blog
                ];
            } else {
                return [
                    "isExecute" => "FAILED",
                    "message" => "Blog not found"
                ];
            }

        } catch (Exception $ex) {
            Log::error("BlogService getById error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error retrieving blog"
            ];
        }
    }

    /**
     * Create a new blog.
     */
    public function create(array $data)
    {
        try {
            $blog = Blog::create($data);

            return [
                "isExecute" => "SUCCESS",
                "message" => "Blog created",
                "data" => $blog
            ];

        } catch (Exception $ex) {
            Log::error("BlogService create error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error creating blog: " . $ex->getMessage()
            ];
        }
    }

    /**
     * Update an existing blog.
     */
    public function update($id, array $data)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return [
                    "isExecute" => "FAILED",
                    "message" => "Blog not found"
                ];
            }

            $blog->update($data);

            return [
                "isExecute" => "SUCCESS",
                "message" => "Blog updated",
                "data" => $blog
            ];

        } catch (Exception $ex) {
            Log::error("BlogService update error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error updating blog"
            ];
        }
    }

    /**
     * Delete a blog by ID.
     */
    public function delete($id)
    {
        try {
            $blog = Blog::find($id);

            if ($blog) {
                $blog->delete();
                return [
                    "isExecute" => "SUCCESS",
                    "message" => "Blog deleted"
                ];
            } else {
                return [
                    "isExecute" => "FAILED",
                    "message" => "Blog not found"
                ];
            }

        } catch (Exception $ex) {
            Log::error("BlogService delete error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error deleting blog"
            ];
        }
    }
}
