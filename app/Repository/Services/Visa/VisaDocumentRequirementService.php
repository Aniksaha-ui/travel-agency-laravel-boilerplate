<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisaDocumentRequirementService
{
    public function getAll($page, $search, $visaTypeId)
    {
        try {
            $query = DB::table('visa_package_required_documents as vprd')
                ->join('visa_packages as vp', 'vprd.visa_package_id', '=', 'vp.id')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vprd.id',
                    'vprd.visa_package_id as visa_type_id',
                    'vprd.document_key',
                    'vprd.document_label as document_name',
                    'vprd.instructions',
                    'vprd.is_required',
                    'vprd.allow_multiple',
                    'vprd.sort_order',
                    'vprd.created_at',
                    'vprd.updated_at',
                    'vp.visa_type as visa_name',
                    'vp.title as visa_title',
                    'vc.name as country_name'
                );

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

            $requirements = $query->orderBy('vprd.sort_order', 'asc')
                ->orderBy('vprd.id', 'asc')
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
            $requirements = DB::table('visa_package_required_documents')
                ->select(
                    'id',
                    'visa_package_id as visa_type_id',
                    'document_key',
                    'document_label as document_name',
                    'instructions',
                    'is_required',
                    'allow_multiple',
                    'sort_order',
                    'created_at',
                    'updated_at'
                )
                ->where('visa_package_id', $visaTypeId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
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
            $documentKey = $data['document_key'] ?? Str::slug($data['document_name'], '_');

            $duplicate = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $data['visa_type_id'])
                ->where(function ($query) use ($documentKey, $data) {
                    $query->where('document_key', $documentKey)
                        ->orWhere('document_label', $data['document_name']);
                })
                ->first();

            if ($duplicate) {
                DB::rollBack();
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Document requirement already exists for the selected visa type',
                ];
            }

            $id = DB::table('visa_package_required_documents')->insertGetId([
                'visa_package_id' => $data['visa_type_id'],
                'document_key' => $documentKey,
                'document_label' => $data['document_name'],
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
                'data' => $this->getPayload($id),
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
            $requirement = $this->getPayload($id);

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
            $existing = DB::table('visa_package_required_documents')->where('id', $id)->first();

            if (!$existing) {
                DB::rollBack();
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa document requirement not found',
                ];
            }

            $documentKey = $data['document_key'] ?? Str::slug($data['document_name'], '_');

            $duplicate = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $data['visa_type_id'])
                ->where('id', '!=', $id)
                ->where(function ($query) use ($documentKey, $data) {
                    $query->where('document_key', $documentKey)
                        ->orWhere('document_label', $data['document_name']);
                })
                ->first();

            if ($duplicate) {
                DB::rollBack();
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Document requirement already exists for the selected visa type',
                ];
            }

            DB::table('visa_package_required_documents')->where('id', $id)->update([
                'visa_package_id' => $data['visa_type_id'],
                'document_key' => $documentKey,
                'document_label' => $data['document_name'],
                'instructions' => $data['instructions'] ?? $existing->instructions,
                'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : $existing->is_required,
                'allow_multiple' => isset($data['allow_multiple']) ? (int) $data['allow_multiple'] : $existing->allow_multiple,
                'sort_order' => $data['sort_order'] ?? $existing->sort_order,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getPayload($id),
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
            $existing = DB::table('visa_package_required_documents')->where('id', $id)->first();

            if (!$existing) {
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

    private function getPayload($id)
    {
        return DB::table('visa_package_required_documents as vprd')
            ->join('visa_packages as vp', 'vprd.visa_package_id', '=', 'vp.id')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vprd.id',
                'vprd.visa_package_id as visa_type_id',
                'vprd.document_key',
                'vprd.document_label as document_name',
                'vprd.instructions',
                'vprd.is_required',
                'vprd.allow_multiple',
                'vprd.sort_order',
                'vprd.created_at',
                'vprd.updated_at',
                'vp.visa_type as visa_name',
                'vp.title as visa_title',
                'vc.name as country_name'
            )
            ->where('vprd.id', $id)
            ->first();
    }
}
