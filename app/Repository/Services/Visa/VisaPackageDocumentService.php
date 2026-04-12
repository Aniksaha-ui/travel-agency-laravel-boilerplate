<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaPackageDocumentService
{
    public function getAll($page, $search, $visaPackageId)
    {
        try {
            $perPage = 10;
            $query = DB::table('visa_package_required_documents as vprd')
                ->join('visa_packages as vp', 'vprd.visa_package_id', '=', 'vp.id')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vprd.*',
                    'vp.title as package_title',
                    'vp.visa_type',
                    'vc.name as country_name'
                );

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vprd.document_key', 'like', '%' . $search . '%')
                        ->orWhere('vprd.document_label', 'like', '%' . $search . '%')
                        ->orWhere('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($visaPackageId)) {
                $query->where('vprd.visa_package_id', $visaPackageId);
            }

            $documents = $query->orderBy('vprd.sort_order', 'asc')
                ->orderBy('vprd.id', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $documents,
                "message" => $documents->total() > 0 ? "Visa package documents retrieved successfully" : "No visa package documents found",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentService getAll error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa package documents",
            ];
        }
    }

    public function getByPackageId($visaPackageId)
    {
        try {
            $documents = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $visaPackageId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $documents,
                "message" => $documents->count() > 0 ? "Visa package documents retrieved successfully" : "No visa package documents found",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentService getByPackageId error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa package documents",
            ];
        }
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $duplicateDocument = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $data['visa_package_id'])
                ->where('document_key', $data['document_key'])
                ->first();

            if ($duplicateDocument) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "This document key already exists for the selected visa package",
                ];
            }

            $documentId = DB::table('visa_package_required_documents')->insertGetId([
                'visa_package_id' => $data['visa_package_id'],
                'document_key' => $data['document_key'],
                'document_label' => $data['document_label'],
                'instructions' => $data['instructions'] ?? null,
                'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : 1,
                'allow_multiple' => isset($data['allow_multiple']) ? (int) $data['allow_multiple'] : 0,
                'sort_order' => $data['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $this->getDocumentPayload($documentId),
                "message" => "Visa package document created successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageDocumentService create error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to create visa package document",
            ];
        }
    }

    public function getById($id)
    {
        try {
            $document = $this->getDocumentPayload($id);

            if (!$document) {
                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package document not found",
                ];
            }

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $document,
                "message" => "Visa package document retrieved successfully",
            ];
        } catch (Exception $exception) {
            Log::error("VisaPackageDocumentService getById error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to retrieve visa package document",
            ];
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $document = DB::table('visa_package_required_documents')->where('id', $id)->first();
            if (!$document) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package document not found",
                ];
            }

            $duplicateDocument = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $data['visa_package_id'])
                ->where('document_key', $data['document_key'])
                ->where('id', '!=', $id)
                ->first();

            if ($duplicateDocument) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "This document key already exists for the selected visa package",
                ];
            }

            DB::table('visa_package_required_documents')->where('id', $id)->update([
                'visa_package_id' => $data['visa_package_id'],
                'document_key' => $data['document_key'],
                'document_label' => $data['document_label'],
                'instructions' => $data['instructions'] ?? null,
                'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : 1,
                'allow_multiple' => isset($data['allow_multiple']) ? (int) $data['allow_multiple'] : 0,
                'sort_order' => $data['sort_order'] ?? 0,
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => $this->getDocumentPayload($id),
                "message" => "Visa package document updated successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageDocumentService update error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to update visa package document",
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $document = DB::table('visa_package_required_documents')->where('id', $id)->first();
            if (!$document) {
                DB::rollBack();

                return [
                    "status" => ApiResponseStatus::FAILED,
                    "data" => [],
                    "message" => "Visa package document not found",
                ];
            }

            DB::table('visa_package_required_documents')->where('id', $id)->delete();
            DB::commit();

            return [
                "status" => ApiResponseStatus::SUCCESS,
                "data" => [],
                "message" => "Visa package document deleted successfully",
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("VisaPackageDocumentService delete error: " . $exception->getMessage());

            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Failed to delete visa package document",
            ];
        }
    }

    private function getDocumentPayload($id)
    {
        return DB::table('visa_package_required_documents as vprd')
            ->join('visa_packages as vp', 'vprd.visa_package_id', '=', 'vp.id')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->select(
                'vprd.*',
                'vp.title as package_title',
                'vp.visa_type',
                'vc.name as country_name'
            )
            ->where('vprd.id', $id)
            ->first();
    }
}
