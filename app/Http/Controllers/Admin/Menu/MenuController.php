<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Repository\Services\Menu\MenuService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $search = $request->query('search');

            $response = $this->menuService->getAll($page, $search);

            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "status" => false,
                "message" => "An error occurred while fetching menu items"
            ], 500);
        }
    }

    /*
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:191',
                'path' => 'nullable|string|max:191',
                'icon' => 'nullable|string|max:191',
                'location' => 'nullable|string|max:191',
                'parent_id' => 'nullable|exists:menu_items,id',
                'order' => 'integer',
                'roles' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $response = $this->menuService->create($request->all());

            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 201); // 201 Created

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "status" => false,
                "message" => "An error occurred while creating menu item"
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $response = $this->menuService->getById($id);

            if (!$response['status']) {
                return response()->json([
                    "status" => false,
                    "message" => $response['message']
                ], 404);
            }

            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "status" => false,
                "message" => "An error occurred while fetching menu item"
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:191',
                'path' => 'nullable|string|max:191',
                'icon' => 'nullable|string|max:191',
                'location' => 'nullable|string|max:191',
                'parent_id' => 'nullable|exists:menu_items,id',
                'order' => 'integer',
                'roles' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $response = $this->menuService->update($id, $request->all());

            if (!$response['status']) {
                return response()->json([
                    "status" => false,
                    "message" => $response['message']
                ], 404);
            }

            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "status" => false,
                "message" => "An error occurred while updating menu item"
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->menuService->delete($id);

            if (!$response['status']) {
                return response()->json([
                    "status" => false,
                    "message" => $response['message']
                ], 404);
            }

            return response()->json([
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                "status" => false,
                "message" => "An error occurred while deleting menu item"
            ], 500);
        }
    }
}
