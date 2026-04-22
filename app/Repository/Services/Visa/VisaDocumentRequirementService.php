<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaDocumentRequirementService
{
    public function getAll($page, $search, $visaTypeId)
    {
        try {
            $query = $this->baseQuery();

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vprd.document_key', 'like', '%' . $search . '%')
                        ->orWhere('vprd.document_label', 'like', '%' . $search . '%')
                        ->orWhere('vp.visa_type', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($visaTypeId)) {
                $query->where('vprd.visa_package_id', $visaTypeId);
            }

            $requirements = $query
                ->orderBy('vc.name', 'asc')
                ->orderBy('vp.visa_type', 'asc')
                ->orderBy('vprd.sort_order', 'asc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $requirements,
                'message' => $requirements->total() > 0 ? 'Visa document requirements retrieved successfully' : 'No visa document requirements found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementService getAll error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa document requirements',
            ];
        }
    }

    public function getByVisaType($visaTypeId)
    {
        try {
            $requirements = $this->baseQuery()
                ->where('vprd.visa_package_id', $visaTypeId)
                ->orderBy('vprd.sort_order', 'asc')
                ->orderBy('vprd.id', 'asc')
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $requirements,
                'message' => $requirements->count() > 0 ? 'Visa document requirements retrieved successfully' : 'No visa document requirements found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementService getByVisaType error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa document requirements',
            ];
        }
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $packageId = $data['visa_package_id'] ?? $data['visa_type_id'];
            $documentKey = $data['document_key'] ?? $this->makeDocumentKey($data['document_name']);

            $duplicate = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $packageId)
                ->where('document_key', $documentKey)
                ->first();

            if ($duplicate) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'This document requirement already exists for the selected visa package',
                ];
            }

            $id = DB::table('visa_package_required_documents')->insertGetId([
                'visa_package_id' => $packageId,
                'document_key' => $documentKey,
                'document_label' => $data['document_label'] ?? $data['document_name'],
                'instructions' => $data['instructions'] ?? null,
                'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : 1,
                'allow_multiple' => isset($data['allow_multiple']) ? (int) $data['allow_multiple'] : 0,
                'sort_order' => $data['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->findById($id),
                'message' => 'Visa document requirement created successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaDocumentRequirementService create error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to create visa document requirement',
            ];
        }
    }

    public function getById($id)
    {
        try {
            $requirement = $this->findById($id);

            if (!$requirement) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa document requirement not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $requirement,
                'message' => 'Visa document requirement retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaDocumentRequirementService getById error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa document requirement',
            ];
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $requirement = DB::table('visa_package_required_documents')->where('id', $id)->first();
            if (!$requirement) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa document requirement not found',
                ];
            }

            $packageId = $data['visa_package_id'] ?? $data['visa_type_id'];
            $documentKey = $data['document_key'] ?? $this->makeDocumentKey($data['document_name']);

            $duplicate = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $packageId)
                ->where('document_key', $documentKey)
                ->where('id', '!=', $id)
                ->first();

            if ($duplicate) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'This document requirement already exists for the selected visa package',
                ];
            }

            DB::table('visa_package_required_documents')->where('id', $id)->update([
                'visa_package_id' => $packageId,
                'document_key' => $documentKey,
                'document_label' => $data['document_label'] ?? $data['document_name'],
                'instructions' => $data['instructions'] ?? null,
                'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : $requirement->is_required,
                'allow_multiple' => isset($data['allow_multiple']) ? (int) $data['allow_multiple'] : $requirement->allow_multiple,
                'sort_order' => $data['sort_order'] ?? $requirement->sort_order,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->findById($id),
                'message' => 'Visa document requirement updated successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaDocumentRequirementService update error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to update visa document requirement',
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $requirement = DB::table('visa_package_required_documents')->where('id', $id)->first();
            if (!$requirement) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa document requirement not found',
                ];
            }

            DB::table('visa_package_required_documents')->where('id', $id)->delete();

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [],
                'message' => 'Visa document requirement deleted successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaDocumentRequirementService delete error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to delete visa document requirement',
            ];
        }
    }

    private function baseQuery()
    {
        return DB::table('visa_package_required_documents as vprd')
            ->join('visa_packages as vp', 'vprd.visa_package_id', '=', 'vp.id')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vprd.id',
                'vprd.visa_package_id as visa_type_id',
                'vprd.visa_package_id',
                'vp.visa_country_id as country_id',
                'vc.name as country_name',
                'vp.visa_type as visa_name',
                'vp.title as package_title',
                'vprd.document_key',
                'vprd.document_label as document_name',
                'vprd.document_label',
                'vprd.instructions',
                'vprd.is_required',
                'vprd.allow_multiple',
                'vprd.sort_order',
                'vprd.created_at',
                'vprd.updated_at'
            );
    }

    private function findById($id)
    {
        return $this->baseQuery()->where('vprd.id', $id)->first();
    }

    private function makeDocumentKey($documentName)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string) $documentName), '_'));
    }
}
