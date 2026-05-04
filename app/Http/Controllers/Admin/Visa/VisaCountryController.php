<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaCountryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisaCountryController extends Controller
{
    private $visaCountryService;

    public function __construct(VisaCountryService $visaCountryService)
    {
        $this->visaCountryService = $visaCountryService;
    }

    public function index(Request $request)
    {
        try {
            $response = $this->visaCountryService->getAll(
                $request->query('page'),
                $request->query('search'),
                $request->query('status')
            );
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaCountryController index error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function dropdown(Request $request)
    {
        try {
            $response = $this->visaCountryService->dropdownList((int) $request->query('active_only', 1) === 1);
            return $this->serviceResponse($response);
        } catch (Exception $exception) {
            Log::error('VisaCountryController dropdown error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'iso_code' => 'nullable|string|max:10',
                'flag' => 'nullable|string|max:255',
                'is_popular' => 'nullable|boolean',
                'status' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaCountryService->create($request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 201));
        } catch (Exception $exception) {
            Log::error('VisaCountryController store error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function show($id)
    {
        try {
            $response = $this->visaCountryService->getById($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response, 200, 404));
        } catch (Exception $exception) {
            Log::error('VisaCountryController show error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'iso_code' => 'nullable|string|max:10',
                'flag' => 'nullable|string|max:255',
                'is_popular' => 'nullable|boolean',
                'status' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse($validator->errors()->first(), 422, [
                    'errors' => $validator->errors(),
                ]);
            }

            $response = $this->visaCountryService->update($id, $request->all());
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaCountryController update error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->visaCountryService->delete($id);
            return $this->serviceResponse($response, $this->serviceStatusCode($response));
        } catch (Exception $exception) {
            Log::error('VisaCountryController destroy error: ' . $exception->getMessage());
            return $this->failedResponse(config('message.server_error'));
        }
    }
}
