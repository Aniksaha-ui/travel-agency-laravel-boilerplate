<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisaTypeService
{
    public function getAll($page, $search, $countryId, $status)
    {
        try {
            $query = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vp.id',
                    'vp.visa_country_id as country_id',
                    'vp.title',
                    'vp.visa_type as visa_name',
                    'vp.processing_days',
                    'vp.fee',
                    'vp.description',
                    'vp.is_active as status',
                    'vc.name as country_name',
                    'vc.iso_code as country_iso_code'
                );

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

            if ($status !== null && $status !== '') {
                $query->where('vp.is_active', (int) $status);
            }

            $visaTypes = $query->orderBy('vc.name', 'asc')
                ->orderBy('vp.title', 'asc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $visaTypes,
                'message' => $visaTypes->total() > 0 ? 'Visa types retrieved successfully' : 'No visa types found',
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

    public function publicList($countryId, $search)
    {
        try {
            $query = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->where('vp.is_active', 1)
                ->where('vc.is_active', 1)
                ->select(
                    'vp.id',
                    'vp.visa_country_id as country_id',
                    'vp.title',
                    'vp.visa_type as visa_name',
                    'vp.processing_days',
                    'vp.fee',
                    'vp.description',
                    'vc.name as country_name',
                    'vc.iso_code as country_iso_code'
                );

            if (!empty($countryId)) {
                $query->where('vp.visa_country_id', $countryId);
            }

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vp.visa_type', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            $visaTypes = $query->orderBy('vc.name', 'asc')
                ->orderBy('vp.title', 'asc')
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $visaTypes,
                'message' => $visaTypes->count() > 0 ? 'Visa types retrieved successfully' : 'No visa types found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaTypeService publicList error: ' . $exception->getMessage());

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
            $query = DB::table('visa_packages')
                ->select('id', 'visa_country_id as country_id', 'title', 'visa_type as visa_name', 'processing_days', 'fee');

            if (!empty($countryId)) {
                $query->where('visa_country_id', $countryId);
            }

            if ($activeOnly) {
                $query->where('is_active', 1);
            }

            $visaTypes = $query->orderBy('title', 'asc')->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $visaTypes,
                'message' => $visaTypes->count() > 0 ? 'Visa type dropdown retrieved successfully' : 'No visa types found',
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
                    'message' => 'Visa type already exists for the selected country',
                ];
            }

            $title = $data['title'] ?? $data['visa_name'];

            $id = DB::table('visa_packages')->insertGetId([
                'visa_country_id' => $data['country_id'],
                'title' => $title,
                'visa_type' => $data['visa_name'],
                'fee' => $data['fee'] ?? 0,
                'currency' => $data['currency'] ?? 'BDT',
                'processing_days' => $data['processing_days'] ?? 0,
                'entry_type' => $data['entry_type'] ?? null,
                'stay_validity_days' => $data['stay_validity_days'] ?? null,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? null,
                'is_active' => isset($data['status']) ? (int) $data['status'] : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getPayload($id),
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
            $visaType = $this->getPayload($id);

            if (!$visaType) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $visaType,
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
            $existing = DB::table('visa_packages')->where('id', $id)->first();

            if (!$existing) {
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
                    'message' => 'Visa type already exists for the selected country',
                ];
            }

            DB::table('visa_packages')->where('id', $id)->update([
                'visa_country_id' => $data['country_id'],
                'title' => $data['title'] ?? $data['visa_name'],
                'visa_type' => $data['visa_name'],
                'fee' => $data['fee'] ?? 0,
                'currency' => $data['currency'] ?? $existing->currency,
                'processing_days' => $data['processing_days'] ?? 0,
                'entry_type' => $data['entry_type'] ?? $existing->entry_type,
                'stay_validity_days' => $data['stay_validity_days'] ?? $existing->stay_validity_days,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? $existing->eligibility,
                'is_active' => isset($data['status']) ? (int) $data['status'] : $existing->is_active,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getPayload($id),
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
            $existing = DB::table('visa_packages')->where('id', $id)->first();

            if (!$existing) {
                DB::rollBack();
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa type not found',
                ];
            }

            $applicationExists = DB::table('visa_applications')->where('visa_package_id', $id)->exists();
            if ($applicationExists) {
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

    private function getPayload($id)
    {
        return DB::table('visa_packages as vp')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vp.id',
                'vp.visa_country_id as country_id',
                'vp.title',
                'vp.visa_type as visa_name',
                'vp.processing_days',
                'vp.fee',
                'vp.description',
                'vp.is_active as status',
                'vc.name as country_name',
                'vc.iso_code as country_iso_code'
            )
            ->where('vp.id', $id)
            ->first();
    }
}
