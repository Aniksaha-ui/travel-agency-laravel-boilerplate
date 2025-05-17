<?php

namespace App\Repository\Services\Guide;

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
}
