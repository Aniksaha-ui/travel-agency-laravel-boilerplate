<?php

namespace App\Repository\Services\Guide;

use App\Helpers\admin\FileManageHelper;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class GuideService
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return array|null
     */
    public function index($page, $search = null)
    {

        try {
            $perPage = 10;
            $guides = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->paginate($perPage, ['guides.*', 'name', 'email'], 'page', $page);
            if ($guides->count() > 0) {
                return ["status" => true, "data" => $guides, "message" => "Guides list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No guides found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal Server Error"];
        }
    }

    public function store($data)
    {
        try {
            DB::beginTransaction();
            $userInformation = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => md5("123456"),
                'role' => 'guide'
            ];

            $userId = DB::table('users')->insertGetId($userInformation);
            if (!$userId) {
                DB::rollBack();
                return ["status" => false, "data" => [], "message" => "Guide not created"];
            }
            $guideInformation = [
                'user_id' => $userId,
                'bio' => $data['bio'],
                'phone' => $data['phone']
            ];
            $guideInformation = DB::table('guides')->insertGetId($guideInformation);

            if ($userInformation && $guideInformation) {
                DB::commit();
                return ["status" => true, "data" => [], "message" => "Guide created successfully"];
            } else {
                DB::rollBack();
                return ["status" => false, "data" => [], "message" => "Guide not created"];
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("guideService store function error:" . $ex->getMessage());
        }
    }


    public function findById($id)
    {
        try {

            Log::info("guideService findById" . $id);
            $guide = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->where('guides.id', $id)
                ->select('guides.*', 'users.name', 'users.email')
                ->first();
            if (!isset($guide)) {
                return ["status" => false, "data" => [], "message" => "Guide not found"];
            } else {
                return ["status" => true, "data" => $guide, "message" => "Guide retrived successfully"];
            }
        } catch (Exception $ex) {
            Log::alert("guideService findById" . $ex->getMessage());
        }
    }

    public function update($data)
    {
        try {
            DB::beginTransaction();
            $userInformation = [
                'name' => $data['name'],
                'email' => $data['email']

            ];
            Log::info("user_id" . json_encode($data['user_id']));

            $userId = DB::table('users')->where('id', $data['user_id'])->update($userInformation);
            Log::info("Update" . json_encode($userId));

            $guideInformation = [
                'bio' => $data['bio'],
                'phone' => $data['phone']
            ];

            $guideInformation = DB::table('guides')->where('user_id', $data['user_id'])->update($guideInformation);

            Log::info("guideService guideInformation" . json_encode($guideInformation));

            if ($guideInformation || $userId) {
                DB::commit();
                return ["status" => true, "data" => [], "message" => "Guide updated successfully"];
            } else {
                DB::rollBack();
                return ["status" => false, "data" => [], "message" => "Guide not updated"];
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("guideService update function error:" . $ex->getMessage());
        }
    }

    public function delete($id)
    {
        try {
        } catch (Exception $ex) {
            Log::alert("Delete Error" . $ex->getMessage());
        }
    }

    public function guidePerformance($data)
    {
        try {
            $guidePerformance = DB::table('guide_performances')->insert($data);
            if ($guidePerformance) {
                return ["status" => true, "data" => [], "message" => "Guide performance created successfully"];
            } else {
                return ["status" => false, "data" => [], "message" => "Guide performance not created"];
            }
        } catch (Exception $ex) {
            Log::info("guideService guidePerformance" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }

    public function getGuidePerformance($page, $search = null)
    {
        try {

            $perPage = 10;
            $guides = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->join('guide_performances', 'guides.id', '=', 'guide_performances.guide_id')
                ->join('packages','guide_performances.package_id','packages.id')
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('packages.name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->paginate($perPage, ['guide_performances.*', 'users.name', 'email','packages.name as package_name'], 'page', $page);
            if ($guides->count() > 0) {
                return ["status" => true, "data" => $guides, "message" => "Guides performance list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No guides found"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidePerformance" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }



    public function getGuidesdropdown()
    {
        try {
            $guides = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->select('guides.id', 'users.name')
                ->get();
            if ($guides->total() > 0) {
                return ["status" => true, "data" => $guides, "message" => "Guides list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No guides found"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidesdropdown" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }

    public function getGuidePackageAssign($page, $search)
    {
        try {
            $perPage = 10;
            $response = DB::table('guide_packages')
                ->join('guides', 'guide_packages.guide_id', '=', 'guides.id')
                ->join('packages', 'guide_packages.package_id', '=', 'packages.id')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('packages.name', 'like', '%' . $search . '%')
                ->paginate($perPage, ['*'], 'page', $page);

            if ($response->count() > 0) {
                return ["status" => true, "data" => $response, "message" => "Guides list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No guides found"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidePackageAssign" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }

    public function myAssignPackages($page, $search)
    {
        try {

            $perPage = 10;
            $guideEmployeeId = DB::table('guides')->where('user_id', Auth::id())->value('id');
            $costingList = DB::table('guide_packages')
                ->join('packages', 'guide_packages.package_id', '=', 'packages.id')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('guide_id', $guideEmployeeId)
                ->select('trips.trip_name', 'packages.name as package_name', 'packages.id as id', 'packages.image')
                ->where(function ($query) use ($search) {
                    $query->where('packages.name', 'like', '%' . $search . '%')
                        ->orWhere('trips.trip_name', 'like', '%' . $search . '%')
                        ->orWhere('packages.name', 'like', '%' . $search . '%');
                })
                ->paginate($perPage, ['*'], 'page', $page);

            if ($costingList->count() > 0) {
                return ["status" => true, "data" => $costingList, "message" => "Package list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No package assigned yet"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidePackageAssign" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }


    public function CostingByPackageList($page, $search, $packageId)
    {
        try {
            $perPage = 10;
            $guideId = Auth::id();
            $costingList = DB::table('trip_package_costings')
                ->join('packages', 'trip_package_costings.package_id', '=', 'packages.id')

                ->where('package_id', $packageId)
                ->where('guide_id', $guideId)
                ->select('trip_package_costings.*', 'packages.name as package_name')
                ->where(function ($query) use ($search) {
                    $query->where('packages.name', 'like', '%' . $search . '%')
                        ->orWhere('trip_package_costings.cost_amount', 'like', '%' . $search . '%')
                        ->orWhere('trip_package_costings.cost_type', 'like', '%' . $search . '%');
                })
                ->paginate($perPage, ['*'], 'page', $page);

            if ($costingList->count() > 0) {
                return ["status" => true, "data" => $costingList, "message" => "Costing list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No costing yet"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidePackageAssign" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }

    public function myFeedBackByPackage($page, $search, $packageId)
    {
        try {

            $perPage = 10;
            $guideEmployeeId = DB::table('guides')->where('user_id', Auth::id())->value('id');
            $costingList = DB::table('guide_performances')
                ->join('packages', 'guide_performances.package_id', '=', 'packages.id')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('guide_id', $guideEmployeeId)
                ->where('package_id', $packageId)
                ->select('guide_performances.*')
                ->where(function ($query) use ($search) {
                    $query->where('packages.name', 'like', '%' . $search . '%')
                        ->orWhere('trips.trip_name', 'like', '%' . $search . '%')
                        ->orWhere('packages.name', 'like', '%' . $search . '%');
                })
                ->paginate($perPage, ['*'], 'page', $page);

            if ($costingList->count() > 0) {
                return ["status" => true, "data" => $costingList, "message" => "Package list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No package assigned yet"];
            }
        } catch (Exception $ex) {
            Log::info("guideService getGuidePackageAssign" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }



    public function costingByPackage($data)
    {
        try {

            $documentLink = '';
            if (request()->hasFile('attachment')) {
                $documentLink = FileManageHelper::uploadFile('travel', request()->file('attachment'));
            }
            $tripId = DB::table('packages')->where('id', $data['package_id'])->value('trip_id');
            $costing = DB::table('trip_package_costings')->insert([
                'trip_id' => $tripId,
                'package_id' => $data['package_id'],
                'guide_id' => $data['guide_id'],
                'cost_type' => $data['cost_type'],
                'cost_amount' => $data['cost_amount'],
                'description' => $data['description'],
                'attachment' => $documentLink,
                'created_at' => now(),
            ]);

            $transactionRef = strtoupper(Str::random(10));
            DB::table('account_history')->insert([
                'user_id' => $data['guide_id'],
                'user_account_type' => 'card',
                'user_account_no' => '01628781323',
                'getaway' => 'card',
                'amount' => $data['cost_amount'],
                'com_account_no' => DB::table('company_accounts')->where('type', 'card')->value('account_number'),
                'transaction_reference' => $transactionRef,
                'transaction_type' => 'd',
                'purpose' => 'package costing',
                'tran_date' => now(),
            ]);
            if ($costing) {
                return ["status" => true, "data" => [], "message" => "Costing created successfully"];
            } else {
                return ["status" => false, "data" => [], "message" => "Costing not created"];
            }
        } catch (Exception $ex) {
            Log::info("guideService costingByPackage" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }

    public function updatePackageCosting($data)
    {
        try {

            DB::beginTransaction();
            $packageInformation = [];
            $documentLink = '';
            if (request()->hasFile('attachment')) {
                $documentLink = FileManageHelper::uploadFile('travel', request()->file('attachment'));
                $packageInformation['attachment'] = $documentLink;
            }


            $packageInformation = [
                'cost_type' => $data['cost_type'],
                'cost_amount' => $data['cost_amount'],
                'description' => $data['description'],
            ];

            $PackageCostingUpdate = DB::table('trip_package_costings')
                ->where('guide_id', Auth::id())
                ->where('package_id', $data['package_id'])
                ->where('id', $data['costing_id'])
                ->update($packageInformation);

            if ($PackageCostingUpdate) {
                DB::commit();
                return ["status" => true, "data" => [], "message" => "Costing updated successfully"];
            } else {
                DB::rollBack();
                return ["status" => false, "data" => [], "message" => "Costing not updated"];
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("updatePackageCosting function error:" . $ex->getMessage());
        }
    }

    public function findCostingById($id)
    {
        try {

            $costingInformation = DB::table('trip_package_costings')->where('id', $id)->get();

            if (!isset($costingInformation)) {
                return ["status" => false, "data" => [], "message" => "Cost details not found"];
            } else {
                return ["status" => true, "data" => $costingInformation, "message" => "Cost details retrived successfully"];
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("updatePackageCosting function error:" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => ""];
        }
    }
}
