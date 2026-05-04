<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaTypeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaTypeController extends Controller
{
    private $visaTypeService;

    public function __construct(VisaTypeService $visaTypeService)
    {
        $this->visaTypeService = $visaTypeService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaTypeService->getAll(
                $request->query('page'),
                $request->query('search'),
                $request->query('country_id', $request->query('visa_country_id')),
                $request->query('status')
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaTypeController index error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function dropdown(Request $request)
    {
        try {
            $response = $this->visaTypeService->dropdownList(
                $request->query('country_id', $request->query('visa_country_id')),
                (int) $request->query('active_only', 1) === 1
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaTypeController dropdown error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:visa_countries,id',
                'visa_name' => 'required|string|max:100',
                'processing_days' => 'nullable|integer|min:0',
                'fee' => 'nullable|numeric|min:0',
                'description' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaTypeService->create($request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 201));
        } catch (Exception $exception) {
            Log::error('VisaTypeController store error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaTypeService->getById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $exception) {
            Log::error('VisaTypeController show error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:visa_countries,id',
                'visa_name' => 'required|string|max:100',
                'processing_days' => 'nullable|integer|min:0',
                'fee' => 'nullable|numeric|min:0',
                'description' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaTypeService->update($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaTypeController update error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaTypeService->delete($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaTypeController destroy error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }
}
