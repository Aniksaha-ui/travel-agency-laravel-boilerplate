<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaTypeService
{
    public function getAll($page, $search, $countryId, $isActive)
    {
        try {
            $query = $this->baseQuery();

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vp.visa_type', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($countryId)) {
                $query->where('vp.visa_country_id', $countryId);
            }

            if ($isActive !== null && $isActive !== '') {
                $query->where('vp.is_active', (int) $isActive);
            }

            $types = $query
                ->orderBy('vc.name', 'asc')
                ->orderBy('vp.processing_days', 'asc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $types,
                'message' => $types->total() > 0 ? 'Visa types retrieved successfully' : 'No visa types found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaTypeService getAll error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa types',
            ];
        }
    }

    public function dropdownList($countryId, $activeOnly)
    {
        try {
            $query = $this->baseQuery();

            if (!empty($countryId)) {
                $query->where('vp.visa_country_id', $countryId);
            }

            if ($activeOnly) {
                $query->where('vp.is_active', 1)->where('vc.is_active', 1);
            }

            $types = $query
                ->orderBy('vc.name', 'asc')
                ->orderBy('vp.processing_days', 'asc')
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $types,
                'message' => $types->count() > 0 ? 'Visa type dropdown retrieved successfully' : 'No visa types found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaTypeService dropdownList error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa type dropdown',
            ];
        }
    }

    public function publicList($countryId, $search)
    {
        return $this->getAll(null, $search, $countryId, 1);
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $duplicate = DB::table('visa_packages')
                ->where('visa_country_id', $data['country_id'])
                ->where('visa_type', $data['visa_name'])
                ->first();

            if ($duplicate) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'A visa type with this name already exists for the selected country',
                ];
            }

            $packageId = DB::table('visa_packages')->insertGetId([
                'visa_country_id' => $data['country_id'],
                'title' => $data['title'] ?? $data['visa_name'],
                'visa_type' => $data['visa_name'],
                'fee' => $data['fee'] ?? 0,
                'currency' => $data['currency'] ?? 'BDT',
                'processing_days' => $data['processing_days'] ?? 1,
                'entry_type' => $data['entry_type'] ?? null,
                'stay_validity_days' => $data['stay_validity_days'] ?? null,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? null,
                'is_active' => isset($data['status']) ? (int) $data['status'] : (isset($data['is_active']) ? (int) $data['is_active'] : 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->findPackageProjection($packageId),
                'message' => 'Visa type created successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaTypeService create error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to create visa type',
            ];
        }
    }

    public function getById($id)
    {
        try {
            $type = $this->findPackageProjection($id);

            if (!$type) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $type,
                'message' => 'Visa type retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaTypeService getById error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa type',
            ];
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $package = DB::table('visa_packages')->where('id', $id)->first();
            if (!$package) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type not found',
                ];
            }

            $duplicate = DB::table('visa_packages')
                ->where('visa_country_id', $data['country_id'])
                ->where('visa_type', $data['visa_name'])
                ->where('id', '!=', $id)
                ->first();

            if ($duplicate) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'A visa type with this name already exists for the selected country',
                ];
            }

            DB::table('visa_packages')->where('id', $id)->update([
                'visa_country_id' => $data['country_id'],
                'title' => $data['title'] ?? $data['visa_name'],
                'visa_type' => $data['visa_name'],
                'fee' => $data['fee'] ?? $package->fee,
                'currency' => $data['currency'] ?? $package->currency,
                'processing_days' => $data['processing_days'] ?? $package->processing_days,
                'entry_type' => $data['entry_type'] ?? $package->entry_type,
                'stay_validity_days' => $data['stay_validity_days'] ?? $package->stay_validity_days,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? $package->eligibility,
                'is_active' => isset($data['status']) ? (int) $data['status'] : (isset($data['is_active']) ? (int) $data['is_active'] : $package->is_active),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->findPackageProjection($id),
                'message' => 'Visa type updated successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaTypeService update error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to update visa type',
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $package = DB::table('visa_packages')->where('id', $id)->first();
            if (!$package) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type not found',
                ];
            }

            if (DB::table('visa_applications')->where('visa_package_id', $id)->exists()) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type has related applications. Deactivate it instead of deleting.',
                ];
            }

            DB::table('visa_packages')->where('id', $id)->delete();

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [],
                'message' => 'Visa type deleted successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaTypeService delete error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to delete visa type',
            ];
        }
    }

    private function baseQuery()
    {
        return DB::table('visa_packages as vp')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vp.id',
                'vp.visa_country_id as country_id',
                'vc.name as country_name',
                'vp.title',
                'vp.visa_type as visa_name',
                'vp.visa_type',
                'vp.fee',
                'vp.currency',
                'vp.processing_days',
                'vp.entry_type',
                'vp.stay_validity_days',
                'vp.description',
                'vp.eligibility',
                'vp.is_active as status',
                'vp.created_at',
                'vp.updated_at'
            );
    }

    private function findPackageProjection($id)
    {
        return $this->baseQuery()->where('vp.id', $id)->first();
    }
}
