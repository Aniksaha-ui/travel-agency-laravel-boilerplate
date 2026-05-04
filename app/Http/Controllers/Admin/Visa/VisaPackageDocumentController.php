<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaPackageDocumentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaPackageDocumentController extends Controller
{
    private $visaPackageDocumentService;

    public function __construct(VisaPackageDocumentService $visaPackageDocumentService)
    {
        $this->visaPackageDocumentService = $visaPackageDocumentService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaPackageDocumentService->getAll(
                $request->query('page'),
                $request->query('search'),
                $request->query('visa_package_id')
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController index error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }

    public function listByPackage($visaPackageId)
    {
        try {
            $response = $this->visaPackageDocumentService->getByPackageId($visaPackageId);
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController listByPackage error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_package_id' => 'required|exists:visa_packages,id',
                'document_key' => 'required|string|max:80|alpha_dash',
                'document_label' => 'required|string|max:120',
                'instructions' => 'nullable|string',
                'is_required' => 'nullable|boolean',
                'allow_multiple' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse("Validation Error", 422, [
                    "errors" => $validator->errors(),
                ]);
            }

            $response = $this->visaPackageDocumentService->create($request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 201));
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController store error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaPackageDocumentService->getById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController show error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_package_id' => 'required|exists:visa_packages,id',
                'document_key' => 'required|string|max:80|alpha_dash',
                'document_label' => 'required|string|max:120',
                'instructions' => 'nullable|string',
                'is_required' => 'nullable|boolean',
                'allow_multiple' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse("Validation Error", 422, [
                    "errors" => $validator->errors(),
                ]);
            }

            $response = $this->visaPackageDocumentService->update($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController update error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaPackageDocumentService->delete($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentController destroy error: " . $exception->getMessage());
            return $this->failedResponse("Internal Server Error");
        }
    }
}
