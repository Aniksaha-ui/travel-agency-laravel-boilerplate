<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaPackageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaPackageController extends Controller
{
    private $visaPackageService;

    public function __construct(VisaPackageService $visaPackageService)
    {
        $this->visaPackageService = $visaPackageService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaPackageService->getAll(
                $request->query('page'),
                $request->query('search'),
                $request->query('visa_country_id'),
                $request->query('visa_type'),
                $request->query('is_active')
            );

            return response()->json($response, 200);
        } catch (Exception $exception) {
            Log::error("VisaPackageController index error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }

    public function dropdown(Request $request)
    {
        try {
            $activeOnly = $request->query('active_only', 1);
            $response = $this->visaPackageService->dropdownList(
                $request->query('visa_country_id'),
                (int) $activeOnly === 1
            );

            return response()->json($response, 200);
        } catch (Exception $exception) {
            Log::error("VisaPackageController dropdown error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_country_id' => 'required|exists:visa_countries,id',
                'title' => 'required|string|max:150',
                'visa_type' => 'required|string|max:50',
                'fee' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10',
                'processing_days' => 'required|integer|min:1',
                'entry_type' => 'nullable|string|max:30',
                'stay_validity_days' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'eligibility' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => ApiResponseStatus::FAILED,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], 422);
            }

            $response = $this->visaPackageService->create($request->all());

            return response()->json($response, $response['status'] === ApiResponseStatus::SUCCESS ? 201 : 422);
        } catch (Exception $exception) {
            Log::error("VisaPackageController store error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaPackageService->getById($id);

            return response()->json($response, $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 404);
        } catch (Exception $exception) {
            Log::error("VisaPackageController show error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_country_id' => 'required|exists:visa_countries,id',
                'title' => 'required|string|max:150',
                'visa_type' => 'required|string|max:50',
                'fee' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10',
                'processing_days' => 'required|integer|min:1',
                'entry_type' => 'nullable|string|max:30',
                'stay_validity_days' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'eligibility' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => ApiResponseStatus::FAILED,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], 422);
            }

            $response = $this->visaPackageService->update($id, $request->all());

            return response()->json($response, $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error("VisaPackageController update error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaPackageService->delete($id);

            return response()->json($response, $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error("VisaPackageController destroy error: " . $exception->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error",
            ], 500);
        }
    }
}
