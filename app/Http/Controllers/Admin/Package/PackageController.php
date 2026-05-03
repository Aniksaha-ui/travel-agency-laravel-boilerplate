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
        try {
            $page = $request->input('page');
            $search = $request->input('search');

            $response = $this->packageService->getAllPackages($page, $search);

            return $this->successResponse($response ?? [], 'List of packages');
        } catch (Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function create(Request $request)
    {

        try {

            $response = $this->packageService->store($request->all());
            if ($response == true) {
                return $this->successResponse($response, 'New Package Created');
            }
        } catch (Exception $ex) {
            return $this->failedResponse('Package Cannot be Created', 200);
        }

        return $this->failedResponse('Package Cannot be Created', 200);
    }
}
