<?php

namespace App\Http\Controllers\User\Visa;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaCountryService;
use App\Repository\Services\Visa\VisaDocumentRequirementService;
use App\Repository\Services\Visa\VisaTypeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisaController extends Controller
{
    private $visaCountryService;
    private $visaTypeService;
    private $visaDocumentRequirementService;

    public function __construct(
        VisaCountryService $visaCountryService,
        VisaTypeService $visaTypeService,
        VisaDocumentRequirementService $visaDocumentRequirementService
    ) {
        $this->visaCountryService = $visaCountryService;
        $this->visaTypeService = $visaTypeService;
        $this->visaDocumentRequirementService = $visaDocumentRequirementService;
    }

    public function countries(Request $request)
    {
        try {
            $response = $this->visaCountryService->publicList($request->query('search'));

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $exception) {
            Log::error('VisaController countries error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function visaTypes(Request $request)
    {
        try {
            $response = $this->visaTypeService->publicList(
                $request->query('country_id'),
                $request->query('search')
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $exception) {
            Log::error('VisaController visaTypes error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function requirements(Request $request)
    {
        try {
            $visaPackageId = $request->query('visa_package_id', $request->query('visa_type_id'));

            if (!$visaPackageId) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => 'visa_package_id is required',
                ], 422);
            }

            $response = $this->visaDocumentRequirementService->getByVisaType($visaPackageId);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $exception) {
            Log::error('VisaController requirements error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }
}
