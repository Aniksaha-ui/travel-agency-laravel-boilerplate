<?php

namespace App\Http\Controllers\Admin\Package;

use App\Http\Controllers\Controller;
use App\Repository\Services\Packages\PackageService;
use Exception;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    protected $packageService;
    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index(Request $request)
    {
        $page = $request->input('page');
        $search = $request->input('search');

        $response = $this->packageService->getAllPackages($page, $search);

        return response()->json([

            'message' => 'List of packages',
            'data' => $response ?? []
        ]);
    }

    public function create(Request $request)
    {

        try {

            $response = $this->packageService->store($request->all());
            if ($response == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => $response,
                    'message' => 'New Package Created',
                ], 200);
            }
        } catch (Exception $ex) {
            return response()->json([
                'isExecute' => false,
                'message' => 'Package Cannot be Created'
            ], 200);
        }
    }
}
