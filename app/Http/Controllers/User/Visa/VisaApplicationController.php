<?php

namespace App\Http\Controllers\User\Visa;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaApplicationService;
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

    public function apply(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'visa_type_id' => 'required|exists:visa_types,id',
                'package_booking_id' => 'nullable|exists:package_bookings,id',
                'booking_id' => 'nullable|exists:bookings,id',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->apply($request->user()->id, $request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 201 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController apply error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function updateApplication(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'nullable|exists:countries,id',
                'visa_type_id' => 'nullable|exists:visa_types,id',
                'package_booking_id' => 'nullable|exists:package_bookings,id',
                'booking_id' => 'nullable|exists:bookings,id',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->updateUserApplication($request->user()->id, $id, $request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController updateApplication error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function deleteApplication(Request $request, $id)
    {
        try {
            $response = $this->visaApplicationService->deleteUserApplication($request->user()->id, $id);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController deleteApplication error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function storeApplicantInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'full_name' => 'required|string|max:150',
                'passport_number' => 'required|string|max:50',
                'passport_expiry' => 'nullable|date',
                'date_of_birth' => 'nullable|date',
                'nationality' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:150',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->storeApplicantInfo($request->user()->id, $request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 201 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController storeApplicantInfo error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function updateApplicantInfo(Request $request, $applicationId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'sometimes|required|string|max:150',
                'passport_number' => 'sometimes|required|string|max:50',
                'passport_expiry' => 'nullable|date',
                'date_of_birth' => 'nullable|date',
                'nationality' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:150',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->updateApplicantInfo($request->user()->id, $applicationId, $request->all());

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController updateApplicantInfo error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function deleteApplicantInfo(Request $request, $applicationId)
    {
        try {
            $response = $this->visaApplicationService->deleteApplicantInfo($request->user()->id, $applicationId);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController deleteApplicantInfo error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function uploadDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'document_type' => 'required|string|max:100',
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->uploadDocument(
                $request->user()->id,
                $request->all(),
                $request->file('file')
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 201 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController uploadDocument error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function updateDocument(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_type' => 'sometimes|required|string|max:100',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->updateDocument(
                $request->user()->id,
                $id,
                $request->all(),
                $request->file('file')
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController updateDocument error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function deleteDocument(Request $request, $id)
    {
        try {
            $response = $this->visaApplicationService->deleteDocument($request->user()->id, $id);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController deleteDocument error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function submit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->submitApplication(
                $request->user()->id,
                $request->visa_application_id,
                $request->remarks
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController submit error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function pay(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'visa_application_id' => 'required|exists:visa_applications,id',
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|string|in:card,bkash,nagad,internet_banking',
                'booking_id' => 'nullable|exists:bookings,id',
                'package_booking_id' => 'nullable|exists:package_bookings,id',
                'bkash' => 'nullable|string|max:50|required_if:payment_method,bkash',
                'nagad' => 'nullable|string|max:50|required_if:payment_method,nagad',
                'card' => 'nullable|string|max:50|required_if:payment_method,card',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'isExecute' => ApiResponseStatus::FAILED,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $response = $this->visaApplicationService->pay(
                $request->user()->id,
                $request->visa_application_id,
                $request->all()
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 422);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController pay error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function myApplications(Request $request)
    {
        try {
            $response = $this->visaApplicationService->myApplications(
                $request->user()->id,
                $request->query('page'),
                $request->query('search'),
                $request->query('status')
            );

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController myApplications error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $response = $this->visaApplicationService->getMyApplicationById($request->user()->id, $id);

            return response()->json([
                'isExecute' => $response['status'],
                'data' => $response['data'],
                'message' => $response['message'],
            ], $response['status'] === ApiResponseStatus::SUCCESS ? 200 : 404);
        } catch (Exception $exception) {
            Log::error('VisaApplicationController show error: ' . $exception->getMessage());

            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config('message.server_error'),
            ], 500);
        }
    }
}
