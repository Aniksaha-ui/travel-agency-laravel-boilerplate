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

            $blogs = $query->select('id', 'title', 'slug', 'author', 'publishDate', 'status', 'coverImage', 'metaDescription', 'created_at', 'updated_at')
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage);

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
     * Get published blogs for public view.
     */
    public function getPublished($page, $search)
    {
        try {
            $perPage = 10;
            $query = Blog::where('status', 'published');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('author', 'like', '%' . $search . '%');
                });
            }

            $blogs = $query->select('id', 'title', 'slug', 'author', 'publishDate', 'status', 'coverImage', 'metaDescription', 'created_at', 'updated_at')
                           ->orderBy('publishDate', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage);

            return [
                "isExecute" => "SUCCESS",
                "data" => $blogs
            ];

        } catch (Exception $ex) {
            Log::error("BlogService getPublished error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error retrieving published blogs"
            ];
        }
    }

    /**
     * Get single published blog by slug.
     */
    public function getBySlug($id)
    {
        try {
            $blog = Blog::where('id', $id)
                        ->where('status', 'published')
                        ->first();

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
            Log::error("BlogService getBySlug error: " . $ex->getMessage());
            return [
                "isExecute" => "FAILED",
                "message" => "Error retrieving blog by slug"
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
