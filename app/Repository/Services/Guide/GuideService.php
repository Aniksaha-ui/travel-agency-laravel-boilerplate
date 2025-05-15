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
        } catch (Exception $ex) {
            Log::alert("Find By Id Error" . $ex->getMessage());
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
