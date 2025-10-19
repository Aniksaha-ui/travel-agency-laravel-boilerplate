<?php

namespace App\Http\Controllers\Admin\guide;

use App\Http\Controllers\Controller;
use App\Repository\Services\Guide\GuideService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    public function guidePerformance(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer|exists:packages,id',
            'guide_id' => 'required|integer|exists:guides,id',
            'rating' => 'required|integer|min:1|max:5',

            'feedback'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            Log::info("guideController guidePerformance" . json_encode($request->all()));
            $response = $this->guideService->guidePerformance($request->all());
            Log::info("guideController guidePerformance response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController guidePerformance" . $ex->getMessage());
        }
    }

    public function getGuidePerformance(Request $request)
    {
        Log::info("guideController getGuidePerformance  qwerty");
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->guideService->getGuidePerformance($page, $search);
            Log::info("guideController response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController getGuidePerformance" . $ex->getMessage());
        }
    }


    public function costingByPackage(Request $request)
    {
        try {
            $request = $request->all();


            $response = $this->guideService->costingByPackage($request);
            Log::info("guideController costingByPackage response" . json_encode($response));
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $ex) {
            Log::error("guideController costingByPackage" . $ex->getMessage());
        }
    }

    public function getGuidesdropdown()
    {
        try {
            $response = $this->guideService->getGuidesdropdown();
            if ($response['status'] == true) {
                return response()->json([
                    "data" => $response['data'],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            } else {
                return response()->json([
                    "data" => [],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            }
        } catch (Exception $ex) {
            Log::error("guideController getGuidesdropdown" . $ex->getMessage());
        }
    }


    public function getGuidePackageAssign(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            Log::info(json_encode($request->page));

            $response = $this->guideService->getGuidePackageAssign($page, $search);
            Log::info(json_encode($response));
            if ($response['status'] == true) {
                return response()->json([
                    "data" => $response['data'],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            } else {
                return response()->json([
                    "data" => [],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            }
        } catch (Exception $ex) {
            Log::error("guideController getGuidePackageAssign" . $ex->getMessage());
        }
    }



    public function costingByPackageList(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $packageId = $request->input('package_id');
            $validator = Validator::make($request->query(), [
                'package_id' => 'required|integer|not_empty',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }


            $response = $this->guideService->CostingByPackageList($page, $search, $packageId);
            return response()->json([
                'isExecute' => true,
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }



    public function myAssignPackages(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');
            $packageId = $request->input('package_id');
            $response = $this->guideService->myAssignPackages($page, $search);
            return response()->json([
                'isExecute' => true,
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No packages found', 'message' => $e->getMessage()], 500);
        }
    }


    public function myFeedBackByPackage(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');
            $packageId = $request->input('package_id');



            $response = $this->guideService->myFeedBackByPackage($page, $search, $packageId);
            return response()->json([
                'isExecute' => true,
                'data' => $response['data'],
                'message' => $response['message'],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No feedbacks found', 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePackageCosting(Request $request)
    {
        try {
            $response = $this->guideService->updatePackageCosting($request->all());
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function findCostingById($id)
    {
        try {

            $response = $this->guideService->findCostingById($id);
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'No feedbacks found', 'message' => $e->getMessage()], 500);
        }
    }
}
