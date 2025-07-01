<?php

namespace App\Repository\Services\Guide;

use App\Helpers\admin\FileManageHelper;
use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

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

            $userId = DB::table('users')->where('id', $data['user_id'])->update($userInformation);

            if (!$userId) {
                DB::rollBack();
                return ["status" => false, "data" => [], "message" => "Guide not updated"];
            }
            $guideInformation = [
                'bio' => $data['bio'],
                'phone' => $data['phone']
            ];
            $guideInformation = DB::table('guides')->where('user_id', $data['user_id'])->update($guideInformation);
            Log::info("guideService guideInformation" . json_encode($guideInformation));

            if ($guideInformation) {
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
            Log::info("guideService getGuidePerformance");

            $perPage = 10;
            $guides = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->join('guide_performances', 'guides.id', '=', 'guide_performances.guide_id')
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->paginate($perPage, ['guide_performances.*', 'name', 'email'], 'page', $page);
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

    public function costingByPackage($data)
    {
        try {

            if (request()->hasFile('attachment')) {
                $documentLink = FileManageHelper::uploadFile('costing', $data['attachment']);
            } else {
                $request['image'] = 'images/trips/default.png';
            }

            $tripId = DB::table('packages')->where('id', $data['package_id'])->value('trip_id');
            $costing = DB::table('trip_package_costings')->insert([
                'trip_id' => $tripId,
                'package_id' => $data['package_id'],
                'guide_id' => $data['guide_id'],
                'cost_type' => $data['cost_type'],
                'cost_amount' => $data['cost_amount'],
                'description' => $data['description'],
                'attachment' => $data['attachment'],
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

    public function getGuidesdropdown()
    {
        try {
            $guides = DB::table('guides')
                ->join('users', 'guides.user_id', '=', 'users.id')
                ->select('guides.id', 'users.name')
                ->get();
            if ($guides->count() > 0) {
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
}
