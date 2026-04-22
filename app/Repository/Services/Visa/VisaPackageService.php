<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaPackageService
{
    public function getAll($page, $search, $visaCountryId, $visaType, $isActive)
    {
        try {
            $perPage = 10;
            $query = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vp.*',
                    'vc.name as country_name',
                    'vc.slug as country_slug',
                    'vc.iso_code as country_iso_code'
                );

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vp.visa_type', 'like', '%' . $search . '%')
                        ->orWhere('vp.entry_type', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($visaCountryId)) {
                $query->where('vp.visa_country_id', $visaCountryId);
            }

            if (!empty($visaType)) {
                $query->where('vp.visa_type', $visaType);
            }

            if ($isActive !== null && $isActive !== '') {
                $query->where('vp.is_active', (int) $isActive);
            }

            $packages = $query->orderBy('vc.name', 'asc')
                ->orderBy('vp.title', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $packages,
                "message" => $packages->total() > 0 ? "Visa packages retrieved successfully" : "No visa packages found",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageService getAll error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa packages",
            ];
        }
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $duplicatePackage = DB::table('visa_packages')
                ->where('visa_country_id', $data['visa_country_id'])
                ->where('title', $data['title'])
                ->first();

            if ($duplicatePackage) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "A visa package with this title already exists for the selected country",
                ];
            }

            $packageId = DB::table('visa_packages')->insertGetId([
                'visa_country_id' => $data['visa_country_id'],
                'title' => $data['title'],
                'visa_type' => $data['visa_type'],
                'fee' => $data['fee'],
                'currency' => $data['currency'] ?? 'BDT',
                'processing_days' => $data['processing_days'],
                'entry_type' => $data['entry_type'] ?? null,
                'stay_validity_days' => $data['stay_validity_days'] ?? null,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? null,
                'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $this->getPackagePayload($packageId),
                "message" => "Visa package created successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageService create error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to create visa package",
            ];
        }
    }

    public function getById($id)
    {
        try {
            $package = $this->getPackagePayload($id);

            if (!$package) {
                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package not found",
                ];
            }

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $package,
                "message" => "Visa package retrieved successfully",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageService getById error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa package",
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
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package not found",
                ];
            }

            $duplicatePackage = DB::table('visa_packages')
                ->where('visa_country_id', $data['visa_country_id'])
                ->where('title', $data['title'])
                ->where('id', '!=', $id)
                ->first();

            if ($duplicatePackage) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "A visa package with this title already exists for the selected country",
                ];
            }

            DB::table('visa_packages')->where('id', $id)->update([
                'visa_country_id' => $data['visa_country_id'],
                'title' => $data['title'],
                'visa_type' => $data['visa_type'],
                'fee' => $data['fee'],
                'currency' => $data['currency'] ?? 'BDT',
                'processing_days' => $data['processing_days'],
                'entry_type' => $data['entry_type'] ?? null,
                'stay_validity_days' => $data['stay_validity_days'] ?? null,
                'description' => $data['description'] ?? null,
                'eligibility' => $data['eligibility'] ?? null,
                'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $this->getPackagePayload($id),
                "message" => "Visa package updated successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageService update error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to update visa package",
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
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package not found",
                ];
            }

            $applicationExists = DB::table('visa_applications')->where('visa_package_id', $id)->exists();
            if ($applicationExists) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package has related applications. Deactivate it instead of deleting.",
                ];
            }

            DB::table('visa_packages')->where('id', $id)->delete();
            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => [],
                "message" => "Visa package deleted successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageService delete error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to delete visa package",
            ];
        }
    }

    public function dropdownList($visaCountryId, $activeOnly)
    {
        try {
            $query = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vp.id',
                    'vp.title',
                    'vp.visa_type',
                    'vp.processing_days',
                    'vp.fee',
                    'vp.currency',
                    'vc.name as country_name'
                );

            if (!empty($visaCountryId)) {
                $query->where('vp.visa_country_id', $visaCountryId);
            }

            if ($activeOnly) {
                $query->where('vp.is_active', 1);
            }

            $packages = $query->orderBy('vc.name', 'asc')
                ->orderBy('vp.title', 'asc')
                ->get();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $packages,
                "message" => $packages->count() > 0 ? "Visa package dropdown retrieved successfully" : "No visa packages found",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageService dropdownList error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa package dropdown",
            ];
        }
    }

    private function getPackagePayload($id)
    {
        $package = DB::table('visa_packages as vp')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vp.*',
                'vc.name as country_name',
                'vc.slug as country_slug',
                'vc.iso_code as country_iso_code'
            )
            ->where('vp.id', $id)
            ->first();

        if ($package) {
            $package->required_documents = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        }

        return $package;
    }
}
