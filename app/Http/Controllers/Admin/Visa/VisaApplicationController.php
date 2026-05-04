<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Constants\VisaApplicationStatus;
use App\Constants\VisaDocumentVerificationStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaApplicationService;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaApplicationController extends Controller
{
    private $visaApplicationService;

    public function __construct(VisaApplicationService $visaApplicationService)
    {
        $this->visaApplicationService = $visaApplicationService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaApplicationService->getApplications(
                $request->query('page'),
                $request->query('search'),
                $request->query('status'),
                $request->query('country_id'),
                $request->query('visa_package_id', $request->query('visa_type_id')),
                $request->query('assigned_to')
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController index error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaApplicationService->getApplicationById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController show error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'nullable|exists:visa_countries,id',
                'visa_type_id' => 'nullable|exists:visa_packages,id',
                'visa_package_id' => 'nullable|exists:visa_packages,id',
                'booking_id' => 'nullable|exists:bookings,id',
                'package_booking_id' => 'nullable|exists:package_bookings,id',
                'assigned_to' => 'nullable|exists:users,id',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaApplicationService->adminUpdateApplication($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController update error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaApplicationService->deleteAdminApplication($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController destroy error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function assign(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'officer_id' => 'required|exists:users,id',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaApplicationService->assignApplication($request->user()->id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController assign error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function documentVerify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_document_id' => 'required|exists:visa_application_documents,id',
                'status' => 'required|in:' . implode(',', VisaDocumentVerificationStatus::all()),
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaApplicationService->verifyDocument($request->user()->id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController documentVerify error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function statusUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'status' => 'required|in:' . implode(',', VisaApplicationStatus::all()),
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaApplicationService->updateStatus($request->user()->id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaApplicationController statusUpdate error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function printApplication($id)
    {
        try {
            $response = $this->visaApplicationService->getPrintableApplication($id);
            if ($this->normalizeExecutionStatus($response['status'] ?? false) !== 'success') {
                return $this->serviceResponse($response, 404);
            }

            $pdf = PDF::loadView('visa.application_pdf', [
                'application' => $response['data'],
            ]);

            return $pdf->download('visa-application-' . $id . '.pdf');
        } catch (Exception $exception) {
            Log::error('VisaApplicationController printApplication error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }
}
