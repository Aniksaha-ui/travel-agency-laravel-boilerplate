<?php

namespace App\Http\Controllers\Admin\Visa;

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
                $request->query('visa_package_id', $request->query('visa_type_id'))
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController index error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_package_id' => 'required_without:visa_type_id|nullable|exists:visa_packages,id',
                'visa_type_id' => 'nullable|exists:visa_packages,id',
                'document_name' => 'required|string|max:100',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaDocumentRequirementService->create($request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 201));
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController store error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaDocumentRequirementService->getById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController show error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_package_id' => 'required_without:visa_type_id|nullable|exists:visa_packages,id',
                'visa_type_id' => 'nullable|exists:visa_packages,id',
                'document_name' => 'required|string|max:100',
                'is_required' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaDocumentRequirementService->update($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController update error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaDocumentRequirementService->delete($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementController destroy error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }
}
