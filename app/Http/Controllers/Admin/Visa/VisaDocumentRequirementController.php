<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaDocumentRequirementService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaDocumentRequirementController extends Controller
{
    private $visaDocumentRequirementService;

    public function __construct(VisaDocumentRequirementService $visaDocumentRequirementService)
    {
        $this->visaDocumentRequirementService = $visaDocumentRequirementService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaDocumentRequirementService->getAll(
                $request->query('page'),
                $request->query('search'),
                $request->query('visa_type_id')
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController index error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_type_id' => 'required|exists:visa_types,id',
                'document_name' => 'required|string|max:100',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaDocumentRequirementService->create($request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 201 : 422);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController store error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaDocumentRequirementService->getById($id);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 404);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController show error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_type_id' => 'required|exists:visa_types,id',
                'document_name' => 'required|string|max:100',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaDocumentRequirementService->update($id, $request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController update error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaDocumentRequirementService->delete($id);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController destroy error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }
}
