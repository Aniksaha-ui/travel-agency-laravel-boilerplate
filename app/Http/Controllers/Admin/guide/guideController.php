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
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info("guideController request" . json_encode($request->all()));
            $response = $this->guideService->store($request->all());
            Log::info("guideController response" . json_encode($response));
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function getGuideById($id)
    {
        try {
            Log::info("guideController getGuideById" . $id);
            $response = $this->guideService->findById($id);
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController getGuideById" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function update(Request $request)
    {
        try {
            Log::info("guideController update" . json_encode($request->all()));
            $response = $this->guideService->update($request->all());
            Log::info("guideController update response" . json_encode($response));
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController" . $ex->getMessage());
            return $this->failedResponse();
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
            return $this->failedResponse('Validation failed', 422, [
                'errors' => $validator->errors(),
            ]);
        }
        try {
            Log::info("guideController guidePerformance" . json_encode($request->all()));
            $response = $this->guideService->guidePerformance($request->all());
            Log::info("guideController guidePerformance response" . json_encode($response));
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController guidePerformance" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function getGuidePerformance(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->guideService->getGuidePerformance($page, $search);
            Log::info("guideController response" . json_encode($response));
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController getGuidePerformance" . $ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function costingByPackage(Request $request)
    {
        try {
            $request = $request->all();
            $response = $this->guideService->costingByPackage($request);
            Log::info("guideController costingByPackage response" . json_encode($response));
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController costingByPackage" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function getGuidesdropdown()
    {
        try {
            $response = $this->guideService->getGuidesdropdown();
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController getGuidesdropdown" . $ex->getMessage());
            return $this->failedResponse();
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
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("guideController getGuidePackageAssign" . $ex->getMessage());
            return $this->failedResponse();
        }
    }



    public function costingByPackageList(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $packageId = $request->input('package_id');
            Log::info(json_encode($packageId));

            $response = $this->guideService->CostingByPackageList($page, $search, $packageId);
            return $this->serviceResponse($response);
        } catch (\Exception $e) {
            Log::error("guideController costingByPackageList" . $e->getMessage());
            return $this->failedResponse();
        }
    }



    public function myAssignPackages(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->guideService->myAssignPackages($page, $search);
            return $this->serviceResponse($response);
        } catch (Exception $e) {
            Log::error("guideController myAssignPackages" . $e->getMessage());
            return $this->failedResponse();
        }
    }


    public function myFeedBackByPackage(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $packageId = $request->input('package_id');

            $response = $this->guideService->myFeedBackByPackage($page, $search, $packageId);
            return $this->serviceResponse($response);
        } catch (Exception $e) {
            Log::error("guideController myFeedBackByPackage" . $e->getMessage());
            return $this->failedResponse();
        }
    }

    public function updatePackageCosting(Request $request)
    {
        try {
            $response = $this->guideService->updatePackageCosting($request->all());
            return $this->serviceResponse($response);
        } catch (Exception $e) {
            Log::error("guideController updatePackageCosting" . $e->getMessage());
            return $this->failedResponse();
        }
    }


    public function findCostingById($id)
    {
        try {
            $response = $this->guideService->findCostingById($id);
            return $this->serviceResponse($response);
        } catch (Exception $e) {
            Log::error("guideController findCostingById" . $e->getMessage());
            return $this->failedResponse();
        }
    }
}
