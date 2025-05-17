<?php

namespace App\Http\Controllers\Admin\guide;

use App\Http\Controllers\Controller;
use App\Repository\Services\Guide\GuideService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class guideController extends Controller
{

    protected $guideService;
    public function __construct(GuideService $guideService)
    {
        $this->guideService = $guideService;
    }
    public function getGuides(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->guideService->index($page, $search);
            Log::info("guideController response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info("guideController request" . json_encode($request->all()));
            // dd($request->name);
            $response = $this->guideService->store($request->all());
            Log::info("guideController response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
        }
    }

    public function getGuideById($id)
    {
        try {
            Log::info("guideController getGuideById" . $id);
            $response = $this->guideService->findById($id);
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController getGuideById" . $ex->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            Log::info("guideController update" . json_encode($request->all()));
            $response = $this->guideService->update($request->all());
            Log::info("guideController update response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
        }
    }
}
