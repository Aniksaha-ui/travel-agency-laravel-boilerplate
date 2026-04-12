<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisaCountryService
{
    public function getAll($page, $search, $isActive)
    {
        try {
            $query = DB::table('visa_countries');

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('iso_code', 'like', '%' . $search . '%')
                        ->orWhere('nationality_name', 'like', '%' . $search . '%');
                });
            }

            if ($isActive !== null && $isActive !== '') {
                $query->where('is_active', (int) $isActive);
            }

            $countries = $query->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $countries,
                'message' => $countries->total() > 0 ? 'Visa countries retrieved successfully' : 'No visa countries found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaCountryService getAll error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa countries',
            ];
        }
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $slug = Str::slug($data['slug'] ?? $data['name']);

            $duplicateCountry = DB::table('visa_countries')->where('slug', $slug)->first();
            if ($duplicateCountry) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country slug already exists',
                ];
            }

            $countryId = DB::table('visa_countries')->insertGetId([
                'name' => $data['name'],
                'slug' => $slug,
                'iso_code' => $data['iso_code'] ?? null,
                'nationality_name' => $data['nationality_name'] ?? $data['name'],
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => DB::table('visa_countries')->where('id', $countryId)->first(),
                'message' => 'Visa country created successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaCountryService create error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to create visa country',
            ];
        }
    }

    public function getById($id)
    {
        try {
            $country = DB::table('visa_countries')->where('id', $id)->first();

            if (!$country) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $country,
                'message' => 'Visa country retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaCountryService getById error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa country',
            ];
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $country = DB::table('visa_countries')->where('id', $id)->first();
            if (!$country) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country not found',
                ];
            }

            $slug = Str::slug($data['slug'] ?? $data['name']);
            $duplicateCountry = DB::table('visa_countries')
                ->where('slug', $slug)
                ->where('id', '!=', $id)
                ->first();

            if ($duplicateCountry) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country slug already exists',
                ];
            }

            DB::table('visa_countries')->where('id', $id)->update([
                'name' => $data['name'],
                'slug' => $slug,
                'iso_code' => $data['iso_code'] ?? null,
                'nationality_name' => $data['nationality_name'] ?? $data['name'],
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => DB::table('visa_countries')->where('id', $id)->first(),
                'message' => 'Visa country updated successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaCountryService update error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to update visa country',
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $country = DB::table('visa_countries')->where('id', $id)->first();
            if (!$country) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country not found',
                ];
            }

            $packageExists = DB::table('visa_packages')->where('visa_country_id', $id)->exists();
            if ($packageExists) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa country has related packages. Deactivate it instead of deleting.',
                ];
            }

            DB::table('visa_countries')->where('id', $id)->delete();
            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [],
                'message' => 'Visa country deleted successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaCountryService delete error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to delete visa country',
            ];
        }
    }

    public function dropdownList($activeOnly)
    {
        try {
            $query = DB::table('visa_countries')->select('id', 'name', 'slug', 'iso_code');

            if ($activeOnly) {
                $query->where('is_active', 1);
            }

            $countries = $query->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $countries,
                'message' => $countries->count() > 0 ? 'Visa country dropdown retrieved successfully' : 'No visa countries found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaCountryService dropdownList error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa country dropdown',
            ];
        }
    }
}
