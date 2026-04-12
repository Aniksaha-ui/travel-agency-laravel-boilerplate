<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use App\Constants\BookingStatus;
use App\Constants\BookingType;
use App\Constants\PaymentForOnline;
use App\Constants\VisaApplicationStatus;
use App\Constants\VisaDocumentVerificationStatus;
use App\Constants\VisaPaymentStatus;
use App\Helpers\admin\FileManageHelper;
use App\Repository\Services\SSLPayment\SSLPaymentService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VisaApplicationService
{
    private $notificationService;
    private $sslPayment;

    public function __construct(VisaNotificationBellService $notificationService, SSLPaymentService $sslPayment)
    {
        $this->notificationService = $notificationService;
        $this->sslPayment = $sslPayment;
    }

    public function getPublicCountries($page, $search)
    {
        try {
            $query = DB::table('visa_countries')->where('is_active', 1);

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nationality_name', 'like', '%' . $search . '%')
                        ->orWhere('iso_code', 'like', '%' . $search . '%');
                });
            }

            $countries = $query->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(20, ['id', 'name', 'slug', 'iso_code', 'nationality_name'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $countries,
                'message' => $countries->total() > 0 ? 'Visa countries retrieved successfully' : 'No visa countries found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getPublicCountries error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa countries',
            ];
        }
    }

    public function getPublicPackages($page, $search, $visaCountryId, $visaType)
    {
        try {
            $query = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->where('vp.is_active', 1)
                ->where('vc.is_active', 1)
                ->select(
                    'vp.id',
                    'vp.title',
                    'vp.visa_type',
                    'vp.fee',
                    'vp.currency',
                    'vp.processing_days',
                    'vp.entry_type',
                    'vp.stay_validity_days',
                    'vp.description',
                    'vc.id as visa_country_id',
                    'vc.name as country_name',
                    'vc.slug as country_slug',
                    'vc.iso_code as country_iso_code'
                );

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vp.visa_type', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($visaCountryId)) {
                $query->where('vp.visa_country_id', $visaCountryId);
            }

            if (!empty($visaType)) {
                $query->where('vp.visa_type', $visaType);
            }

            $packages = $query->orderBy('vc.name', 'asc')
                ->orderBy('vp.processing_days', 'asc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $packages,
                'message' => $packages->total() > 0 ? 'Visa packages retrieved successfully' : 'No visa packages found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getPublicPackages error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa packages',
            ];
        }
    }

    public function getPublicPackageById($id)
    {
        try {
            $package = $this->getPackagePayload($id, true);

            if (!$package) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa package not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $package,
                'message' => 'Visa package retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getPublicPackageById error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa package',
            ];
        }
    }

    public function createApplication($user, $data)
    {
        DB::beginTransaction();

        try {
            $package = DB::table('visa_packages as vp')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->where('vp.id', $data['visa_package_id'])
                ->where('vp.is_active', 1)
                ->where('vc.is_active', 1)
                ->select('vp.*', 'vc.name as country_name')
                ->first();

            if (!$package) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Selected visa package is not available',
                ];
            }

            $applicationNo = $this->generateApplicationNo();

            $applicationId = DB::table('visa_applications')->insertGetId([
                'user_id' => $user->id,
                'visa_package_id' => $package->id,
                'application_no' => $applicationNo,
                'full_name' => $data['full_name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'present_address' => $data['present_address'] ?? null,
                'travel_purpose' => $data['travel_purpose'] ?? null,
                'travel_date' => $data['travel_date'] ?? null,
                'passport_no' => $data['passport_no'],
                'passport_issue_date' => $data['passport_issue_date'] ?? null,
                'passport_expiry_date' => $data['passport_expiry_date'] ?? null,
                'country_name_snapshot' => $package->country_name,
                'visa_type_snapshot' => $package->visa_type,
                'fee_snapshot' => $package->fee,
                'currency_snapshot' => $package->currency,
                'processing_days_snapshot' => $package->processing_days,
                'status' => VisaApplicationStatus::PENDING,
                'payment_status' => VisaPaymentStatus::PENDING,
                'submitted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->addStatusLog($applicationId, null, VisaApplicationStatus::PENDING, $user->id, 'Visa application submitted');
            $this->notificationService->create(
                $user->id,
                'Visa application submitted',
                'Your visa application ' . $applicationNo . ' has been submitted successfully.',
                $applicationId
            );

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getApplicationPayload($applicationId),
                'message' => 'Visa application submitted successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService createApplication error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to submit visa application',
            ];
        }
    }

    public function getUserApplications($userId, $page, $status, $search)
    {
        try {
            $query = $this->applicationListingQuery()->where('va.user_id', $userId);

            if (!empty($status)) {
                $query->where('va.status', $status);
            }

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('va.application_no', 'like', '%' . $search . '%')
                        ->orWhere('va.passport_no', 'like', '%' . $search . '%')
                        ->orWhere('vp.title', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%');
                });
            }

            $applications = $query->orderBy('va.id', 'desc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $applications,
                'message' => $applications->total() > 0 ? 'Visa applications retrieved successfully' : 'No visa applications found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getUserApplications error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa applications',
            ];
        }
    }

    public function getUserApplicationById($userId, $applicationId)
    {
        return $this->getOwnedApplicationPayload($userId, $applicationId);
    }

    public function getUserTimeline($userId, $applicationId)
    {
        try {
            $application = DB::table('visa_applications')
                ->where('id', $applicationId)
                ->where('user_id', $userId)
                ->first();

            if (!$application) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            $timeline = DB::table('visa_application_status_logs as vasl')
                ->leftJoin('users as u', 'vasl.changed_by', '=', 'u.id')
                ->where('vasl.visa_application_id', $applicationId)
                ->orderBy('vasl.id', 'asc')
                ->select(
                    'vasl.id',
                    'vasl.old_status',
                    'vasl.new_status',
                    'vasl.note',
                    'vasl.created_at',
                    'u.name as changed_by_name'
                )
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $timeline,
                'message' => $timeline->count() > 0 ? 'Visa application timeline retrieved successfully' : 'No timeline found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getUserTimeline error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa application timeline',
            ];
        }
    }

    public function uploadDocuments($applicationId, $userId, $data, $files)
    {
        DB::beginTransaction();

        try {
            $application = DB::table('visa_applications')
                ->where('id', $applicationId)
                ->where('user_id', $userId)
                ->first();

            if (!$application) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            if (empty($files)) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'No files uploaded',
                ];
            }

            $documentKeys = $data['document_keys'] ?? [];
            $documentLabels = $data['document_labels'] ?? [];
            $uploadedDocuments = [];

            foreach ($files as $index => $file) {
                $documentKey = $documentKeys[$index] ?? null;
                $documentLabel = $documentLabels[$index] ?? $documentKey;

                $requiredDocument = null;
                if ($documentKey) {
                    $requiredDocument = DB::table('visa_package_required_documents')
                        ->where('visa_package_id', $application->visa_package_id)
                        ->where('document_key', $documentKey)
                        ->first();
                }

                $filePath = FileManageHelper::uploadFileUnderCurrentDate(
                    'visas/applications/' . $applicationId . '/documents',
                    $file
                );

                $documentId = DB::table('visa_application_documents')->insertGetId([
                    'visa_application_id' => $applicationId,
                    'visa_package_required_document_id' => $requiredDocument->id ?? null,
                    'uploaded_by' => $userId,
                    'document_key' => $documentKey ?? 'document_' . ($index + 1),
                    'document_label' => $requiredDocument->document_label ?? ($documentLabel ?: 'Document ' . ($index + 1)),
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'verification_status' => VisaDocumentVerificationStatus::PENDING,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $uploadedDocuments[] = DB::table('visa_application_documents')->where('id', $documentId)->first();
            }

            DB::table('visa_applications')->where('id', $applicationId)->update([
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $uploadedDocuments,
                'message' => 'Visa application documents uploaded successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService uploadDocuments error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to upload visa documents',
            ];
        }
    }

    public function initiatePayment($applicationId, $user, $paymentInfo)
    {
        DB::beginTransaction();

        try {
            $application = DB::table('visa_applications')
                ->where('id', $applicationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$application) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            if ($application->payment_status === VisaPaymentStatus::PAID) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application payment is already completed',
                ];
            }

            if ($application->status === VisaApplicationStatus::REJECTED) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Rejected applications cannot be paid',
                ];
            }

            if ((float) $paymentInfo['amount'] !== (float) $application->fee_snapshot) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Payment amount must match the visa fee',
                ];
            }

            $isOnlinePayment = (int) DB::table('payment_method_config')
                ->where('payment_for', PaymentForOnline::VISA)
                ->value('online_payment') === 1;

            $bookingId = $this->createOrReuseBooking(
                $application,
                $user->id,
                $isOnlinePayment ? BookingStatus::PENDING : BookingStatus::PAID
            );

            if ((int) $application->booking_id !== (int) $bookingId) {
                DB::table('visa_applications')->where('id', $applicationId)->update([
                    'booking_id' => $bookingId,
                    'updated_at' => now(),
                ]);
            }

            $existingPayment = DB::table('payments')->where('booking_id', $bookingId)->orderBy('id', 'desc')->first();
            if ($existingPayment && in_array($application->payment_status, [VisaPaymentStatus::PENDING, VisaPaymentStatus::PAID], true)) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Payment has already been initiated for this application',
                ];
            }

            $paymentId = DB::table('payments')->insertGetId([
                'booking_id' => $bookingId,
                'amount' => $paymentInfo['amount'],
                'payment_method' => $paymentInfo['payment_method'],
                'bkash' => $paymentInfo['bkash'] ?? null,
                'nagad' => $paymentInfo['nagad'] ?? null,
                'card' => $paymentInfo['card'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transactionReference = strtoupper(Str::random(12));
            $transactionId = DB::table('transactions')->insertGetId([
                'payment_id' => $paymentId,
                'transaction_reference' => $transactionReference,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('visa_applications')->where('id', $applicationId)->update([
                'payment_status' => VisaPaymentStatus::PENDING,
                'updated_at' => now(),
            ]);

            if ($isOnlinePayment) {
                $postData = [
                    'store_id' => env('STORE_ID'),
                    'store_passwd' => env('STORE_PASSWORD'),
                    'total_amount' => $paymentInfo['amount'],
                    'currency' => 'BDT',
                    'tran_id' => $transactionId,
                    'success_url' => route('visa.payment.success'),
                    'fail_url' => route('visa.payment.fail'),
                    'cancel_url' => route('visa.payment.cancel'),
                    'emi_option' => 0,
                    'cus_name' => $application->full_name,
                    'cus_email' => $application->email ?? $user->email,
                    'cus_add1' => $application->present_address ?? 'Dhaka, Bangladesh',
                    'cus_city' => 'Dhaka',
                    'cus_state' => 'Dhaka',
                    'cus_postcode' => '1200',
                    'cus_country' => 'Bangladesh',
                    'cus_phone' => $application->phone ?? '0000000000',
                    'shipping_method' => 'NO',
                    'product_name' => 'Visa Application #' . $application->application_no,
                    'product_category' => 'Visa',
                    'product_profile' => 'general',
                    'value_a' => $applicationId,
                    'value_b' => $bookingId,
                ];

                $initPayment = $this->sslPayment->initSSLTransaction($postData);
                if ($initPayment['status'] !== 'success') {
                    DB::rollBack();

                    return [
                        'status' => ApiResponseStatus::FAILED,
                        'data' => [],
                        'message' => $initPayment['message'] ?? 'Failed to initialize online payment',
                    ];
                }

                DB::commit();

                return [
                    'status' => ApiResponseStatus::SUCCESS,
                    'data' => [
                        'application_id' => $applicationId,
                        'booking_id' => $bookingId,
                        'payment_id' => $paymentId,
                        'transaction_id' => $transactionId,
                        'transaction_reference' => $transactionReference,
                        'redirected_url' => $initPayment['url'],
                    ],
                    'message' => 'Visa payment initialized successfully',
                ];
            }

            $this->creditCompanyAccount($user->id, $paymentInfo, $transactionReference, 'visa application');
            DB::table('bookings')->where('id', $bookingId)->update([
                'status' => BookingStatus::PAID,
                'updated_at' => now(),
            ]);

            DB::table('visa_applications')->where('id', $applicationId)->update([
                'payment_status' => VisaPaymentStatus::PAID,
                'updated_at' => now(),
            ]);

            $this->notificationService->create(
                $user->id,
                'Visa payment successful',
                'Your visa fee payment for application ' . $application->application_no . ' has been completed.',
                $applicationId
            );

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [
                    'application_id' => $applicationId,
                    'booking_id' => $bookingId,
                    'payment_id' => $paymentId,
                    'transaction_id' => $transactionId,
                    'transaction_reference' => $transactionReference,
                ],
                'message' => 'Visa payment completed successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService initiatePayment error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to process visa payment',
            ];
        }
    }

    public function handlePaymentSuccess($request)
    {
        DB::beginTransaction();

        try {
            $verifyUrl = env('IS_SANDBOX')
                ? 'https://dev-securepay.sslcommerz.com/validator/api/validationserverAPI.php'
                : 'https://dev-securepay.sslcommerz.com/validator/api/validationserverAPI.php';

            $verifyResponse = Http::get($verifyUrl, [
                'val_id' => $request->val_id,
                'store_id' => env('STORE_ID'),
                'store_passwd' => env('STORE_PASSWORD'),
                'v' => 1,
                'format' => 'json',
            ]);

            $verifyData = $verifyResponse->json();
            if (!isset($verifyData['status']) || !in_array($verifyData['status'], ['VALID', 'VALIDATED'], true)) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'redirect_url' => $this->buildFrontendUrl('/visa-applications?payment=failed'),
                    'message' => 'Payment verification failed',
                ];
            }

            $applicationId = $verifyData['value_a'] ?? null;
            $bookingId = $verifyData['value_b'] ?? null;
            $transactionId = $request->tran_id;

            $application = DB::table('visa_applications')->where('id', $applicationId)->first();
            if (!$application) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'redirect_url' => $this->buildFrontendUrl('/visa-applications?payment=failed'),
                    'message' => 'Visa application not found',
                ];
            }

            $transactionData = $this->filterTransactionUpdateData([
                'val_id' => $verifyData['val_id'] ?? null,
                'settled_amount' => $verifyData['store_amount'] ?? null,
                'customer_paid_amount' => $verifyData['amount'] ?? null,
                'bank_transaction_id' => $verifyData['bank_tran_id'] ?? null,
                'card_type' => $verifyData['card_type'] ?? null,
                'card_brand' => $verifyData['card_brand'] ?? null,
                'risk_title' => $verifyData['risk_title'] ?? null,
                'settlement_status' => $verifyData['settlement_status'] ?? null,
                'bank_approval_id' => $verifyData['bank_approval_id'] ?? null,
                'customer_name' => $verifyData['cus_name'] ?? null,
                'customer_email' => $verifyData['cus_email'] ?? null,
                'updated_at' => now(),
            ]);

            if (!empty($transactionData)) {
                DB::table('transactions')->where('id', $transactionId)->update($transactionData);
            }

            if ($application->payment_status !== VisaPaymentStatus::PAID) {
                $payment = DB::table('payments')->where('booking_id', $bookingId)->orderBy('id', 'desc')->first();
                $transactionReference = DB::table('transactions')->where('id', $transactionId)->value('transaction_reference');

                if ($payment) {
                    $this->creditCompanyAccount($application->user_id, [
                        'payment_method' => $payment->payment_method,
                        'amount' => $payment->amount,
                        'bkash' => $payment->bkash,
                        'nagad' => $payment->nagad,
                        'card' => $payment->card,
                    ], $transactionReference, 'visa application');
                }

                DB::table('bookings')->where('id', $bookingId)->update([
                    'status' => BookingStatus::PAID,
                    'updated_at' => now(),
                ]);

                DB::table('visa_applications')->where('id', $applicationId)->update([
                    'payment_status' => VisaPaymentStatus::PAID,
                    'updated_at' => now(),
                ]);

                $this->notificationService->create(
                    $application->user_id,
                    'Visa payment successful',
                    'Your online payment for visa application ' . $application->application_no . ' has been verified successfully.',
                    $applicationId
                );
            }

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'redirect_url' => $this->buildFrontendUrl('/visa-applications/' . $applicationId . '?payment=success'),
                'message' => 'Payment completed successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService handlePaymentSuccess error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'redirect_url' => $this->buildFrontendUrl('/visa-applications?payment=failed'),
                'message' => 'Failed to complete visa payment',
            ];
        }
    }

    public function handlePaymentFailure($request, $paymentStatus)
    {
        DB::beginTransaction();

        try {
            $applicationId = $request->value_a ?? null;
            $bookingId = $request->value_b ?? null;

            if ($applicationId) {
                $application = DB::table('visa_applications')->where('id', $applicationId)->first();
                if ($application) {
                    DB::table('visa_applications')->where('id', $applicationId)->update([
                        'payment_status' => $paymentStatus,
                        'updated_at' => now(),
                    ]);

                    if ($bookingId) {
                        DB::table('bookings')->where('id', $bookingId)->update([
                            'status' => BookingStatus::PENDING,
                            'updated_at' => now(),
                        ]);
                    }

                    $this->notificationService->create(
                        $application->user_id,
                        'Visa payment ' . $paymentStatus,
                        'Your visa payment for application ' . $application->application_no . ' is marked as ' . $paymentStatus . '.',
                        $applicationId
                    );
                }
            }

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'redirect_url' => $this->buildFrontendUrl('/visa-applications/' . $applicationId . '?payment=' . $paymentStatus),
                'message' => 'Visa payment status updated',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService handlePaymentFailure error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'redirect_url' => $this->buildFrontendUrl('/visa-applications?payment=' . $paymentStatus),
                'message' => 'Failed to update visa payment status',
            ];
        }
    }

    public function getAdminApplications($page, $search, $status, $visaCountryId, $userId)
    {
        try {
            $query = $this->applicationListingQuery();

            if (!empty($search)) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('va.application_no', 'like', '%' . $search . '%')
                        ->orWhere('va.passport_no', 'like', '%' . $search . '%')
                        ->orWhere('u.name', 'like', '%' . $search . '%')
                        ->orWhere('u.email', 'like', '%' . $search . '%')
                        ->orWhere('vc.name', 'like', '%' . $search . '%')
                        ->orWhere('vp.title', 'like', '%' . $search . '%');
                });
            }

            if (!empty($status)) {
                $query->where('va.status', $status);
            }

            if (!empty($visaCountryId)) {
                $query->where('vc.id', $visaCountryId);
            }

            if (!empty($userId)) {
                $query->where('u.id', $userId);
            }

            $applications = $query->orderBy('va.id', 'desc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $applications,
                'message' => $applications->total() > 0 ? 'Visa applications retrieved successfully' : 'No visa applications found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getAdminApplications error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa applications',
            ];
        }
    }

    public function getAdminApplicationById($applicationId)
    {
        try {
            $application = $this->getApplicationPayload($applicationId);

            if (!$application) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $application,
                'message' => 'Visa application retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getAdminApplicationById error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa application',
            ];
        }
    }

    public function reviewDocument($documentId, $adminId, $verificationStatus, $remarks)
    {
        DB::beginTransaction();

        try {
            $document = DB::table('visa_application_documents')->where('id', $documentId)->first();
            if (!$document) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa document not found',
                ];
            }

            DB::table('visa_application_documents')->where('id', $documentId)->update([
                'verification_status' => $verificationStatus,
                'reviewed_by' => $adminId,
                'remarks' => $remarks,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            $application = DB::table('visa_applications')->where('id', $document->visa_application_id)->first();
            if ($application) {
                $this->notificationService->create(
                    $application->user_id,
                    'Visa document reviewed',
                    'One of your visa documents for application ' . $application->application_no . ' was marked as ' . $verificationStatus . '.',
                    $application->id
                );
            }

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => DB::table('visa_application_documents')->where('id', $documentId)->first(),
                'message' => 'Visa document reviewed successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService reviewDocument error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to review visa document',
            ];
        }
    }

    public function updateStatus($applicationId, $adminId, $newStatus, $note)
    {
        DB::beginTransaction();

        try {
            $application = DB::table('visa_applications')->where('id', $applicationId)->first();
            if (!$application) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            $updateData = [
                'status' => $newStatus,
                'admin_note' => $note,
                'updated_at' => now(),
            ];

            if ($newStatus === VisaApplicationStatus::APPROVED) {
                $updateData['approved_at'] = now();
            }

            if ($newStatus === VisaApplicationStatus::REJECTED) {
                $updateData['rejected_at'] = now();
            }

            DB::table('visa_applications')->where('id', $applicationId)->update($updateData);
            $this->addStatusLog($applicationId, $application->status, $newStatus, $adminId, $note);

            $this->notificationService->create(
                $application->user_id,
                'Visa status updated',
                'Your visa application ' . $application->application_no . ' status is now ' . $newStatus . '.',
                $applicationId
            );

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getApplicationPayload($applicationId),
                'message' => 'Visa application status updated successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService updateStatus error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to update visa application status',
            ];
        }
    }

    public function uploadResult($applicationId, $adminId, $data, $approvedVisaFile = null, $rejectionLetterFile = null)
    {
        DB::beginTransaction();

        try {
            $application = DB::table('visa_applications')->where('id', $applicationId)->first();
            if (!$application) {
                DB::rollBack();

                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            $oldStatus = $application->status;
            $updateData = [
                'result_uploaded_by' => $adminId,
                'updated_at' => now(),
            ];

            if ($data['result_type'] === 'approved') {
                if ($approvedVisaFile) {
                    $updateData['approved_visa_file_path'] = FileManageHelper::uploadFileUnderCurrentDate(
                        'visas/applications/' . $applicationId . '/results',
                        $approvedVisaFile
                    );
                }

                $updateData['status'] = VisaApplicationStatus::APPROVED;
                $updateData['approved_at'] = now();
                $updateData['rejection_reason'] = null;
                $updateData['rejection_letter_file_path'] = null;
            }

            if ($data['result_type'] === 'rejected') {
                if ($rejectionLetterFile) {
                    $updateData['rejection_letter_file_path'] = FileManageHelper::uploadFileUnderCurrentDate(
                        'visas/applications/' . $applicationId . '/results',
                        $rejectionLetterFile
                    );
                }

                $updateData['status'] = VisaApplicationStatus::REJECTED;
                $updateData['rejected_at'] = now();
                $updateData['rejection_reason'] = $data['rejection_reason'];
            }

            DB::table('visa_applications')->where('id', $applicationId)->update($updateData);
            $this->addStatusLog($applicationId, $oldStatus, $updateData['status'], $adminId, $data['note'] ?? null);

            $this->notificationService->create(
                $application->user_id,
                'Visa application result updated',
                'Your visa application ' . $application->application_no . ' result has been updated to ' . $updateData['status'] . '.',
                $applicationId
            );

            DB::commit();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $this->getApplicationPayload($applicationId),
                'message' => 'Visa result uploaded successfully',
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VisaApplicationService uploadResult error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to upload visa result',
            ];
        }
    }

    public function getReportSummary($dateFrom, $dateTo)
    {
        try {
            $baseQuery = DB::table('visa_applications as va');

            if (!empty($dateFrom)) {
                $baseQuery->whereDate('va.created_at', '>=', $dateFrom);
            }

            if (!empty($dateTo)) {
                $baseQuery->whereDate('va.created_at', '<=', $dateTo);
            }

            $totalApplications = (clone $baseQuery)->count();
            $approvedApplications = (clone $baseQuery)->where('va.status', VisaApplicationStatus::APPROVED)->count();
            $rejectedApplications = (clone $baseQuery)->where('va.status', VisaApplicationStatus::REJECTED)->count();
            $pendingApplications = (clone $baseQuery)->where('va.status', VisaApplicationStatus::PENDING)->count();
            $processingApplications = (clone $baseQuery)->where('va.status', VisaApplicationStatus::PROCESSING)->count();

            $revenueQuery = DB::table('visa_applications as va')
                ->leftJoin('payments as p', 'va.booking_id', '=', 'p.booking_id');

            if (!empty($dateFrom)) {
                $revenueQuery->whereDate('va.created_at', '>=', $dateFrom);
            }

            if (!empty($dateTo)) {
                $revenueQuery->whereDate('va.created_at', '<=', $dateTo);
            }

            $totalRevenue = $revenueQuery
                ->where('va.payment_status', VisaPaymentStatus::PAID)
                ->sum('p.amount');

            $countryBreakdown = DB::table('visa_applications as va')
                ->join('visa_packages as vp', 'va.visa_package_id', '=', 'vp.id')
                ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
                ->select(
                    'vc.name as country_name',
                    DB::raw('COUNT(va.id) as total_applications'),
                    DB::raw("SUM(CASE WHEN va.status = '" . VisaApplicationStatus::APPROVED . "' THEN 1 ELSE 0 END) as approved_applications"),
                    DB::raw("SUM(CASE WHEN va.status = '" . VisaApplicationStatus::REJECTED . "' THEN 1 ELSE 0 END) as rejected_applications")
                )
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    return $query->whereDate('va.created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    return $query->whereDate('va.created_at', '<=', $dateTo);
                })
                ->groupBy('vc.name')
                ->orderByDesc('total_applications')
                ->get();

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [
                    'total_applications' => $totalApplications,
                    'approved_applications' => $approvedApplications,
                    'rejected_applications' => $rejectedApplications,
                    'pending_applications' => $pendingApplications,
                    'processing_applications' => $processingApplications,
                    'total_revenue' => $totalRevenue,
                    'country_breakdown' => $countryBreakdown,
                ],
                'message' => 'Visa report summary retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getReportSummary error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa report summary',
            ];
        }
    }

    private function createOrReuseBooking($application, $userId, $status)
    {
        if ($application->booking_id) {
            DB::table('bookings')->where('id', $application->booking_id)->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

            return $application->booking_id;
        }

        return DB::table('bookings')->insertGetId([
            'user_id' => $userId,
            'status' => $status,
            'booking_type' => BookingType::VISA,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function creditCompanyAccount($userId, $paymentInfo, $transactionReference, $purpose)
    {
        $companyAccountNo = DB::table('company_accounts')
            ->where('type', $paymentInfo['payment_method'])
            ->value('account_number');

        DB::table('company_accounts')
            ->where('type', $paymentInfo['payment_method'])
            ->increment('amount', $paymentInfo['amount']);

        DB::table('account_history')->insert([
            'user_id' => $userId,
            'user_account_type' => $paymentInfo['payment_method'],
            'user_account_no' => $this->resolveUserAccountNumber($paymentInfo),
            'getaway' => $paymentInfo['payment_method'],
            'amount' => $paymentInfo['amount'],
            'com_account_no' => $companyAccountNo,
            'transaction_reference' => $transactionReference,
            'transaction_type' => 'c',
            'purpose' => $purpose,
            'tran_date' => now(),
        ]);
    }

    private function resolveUserAccountNumber($paymentInfo)
    {
        if (($paymentInfo['payment_method'] ?? null) === 'card') {
            return $paymentInfo['card'] ?? null;
        }

        if (($paymentInfo['payment_method'] ?? null) === 'nagad') {
            return $paymentInfo['nagad'] ?? null;
        }

        if (($paymentInfo['payment_method'] ?? null) === 'bkash') {
            return $paymentInfo['bkash'] ?? null;
        }

        return null;
    }

    private function addStatusLog($applicationId, $oldStatus, $newStatus, $changedBy, $note)
    {
        DB::table('visa_application_status_logs')->insert([
            'visa_application_id' => $applicationId,
            'changed_by' => $changedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function applicationListingQuery()
    {
        return DB::table('visa_applications as va')
            ->join('visa_packages as vp', 'va.visa_package_id', '=', 'vp.id')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->join('users as u', 'va.user_id', '=', 'u.id')
            ->select(
                'va.id',
                'va.application_no',
                'va.full_name',
                'va.email',
                'va.phone',
                'va.passport_no',
                'va.travel_date',
                'va.status',
                'va.payment_status',
                'va.fee_snapshot',
                'va.currency_snapshot',
                'va.created_at',
                'va.updated_at',
                'vp.id as visa_package_id',
                'vp.title as package_title',
                'vp.visa_type',
                'vc.id as visa_country_id',
                'vc.name as country_name',
                'u.id as user_id',
                'u.name as user_name',
                'u.email as user_email'
            );
    }

    private function getOwnedApplicationPayload($userId, $applicationId)
    {
        try {
            $application = $this->getApplicationPayload($applicationId);

            if (!$application || (int) $application->user_id !== (int) $userId) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Visa application not found',
                ];
            }

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $application,
                'message' => 'Visa application retrieved successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaApplicationService getOwnedApplicationPayload error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa application',
            ];
        }
    }

    private function getApplicationPayload($applicationId)
    {
        $application = DB::table('visa_applications as va')
            ->join('visa_packages as vp', 'va.visa_package_id', '=', 'vp.id')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->join('users as u', 'va.user_id', '=', 'u.id')
            ->leftJoin('users as result_user', 'va.result_uploaded_by', '=', 'result_user.id')
            ->where('va.id', $applicationId)
            ->select(
                'va.*',
                'u.id as user_id',
                'vp.title as package_title',
                'vp.visa_type as package_visa_type',
                'vp.description as package_description',
                'vp.eligibility as package_eligibility',
                'vc.name as country_name',
                'vc.slug as country_slug',
                'vc.iso_code as country_iso_code',
                'u.name as user_name',
                'u.email as user_email',
                'result_user.name as result_uploaded_by_name'
            )
            ->first();

        if ($application) {
            $application->required_documents = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $application->visa_package_id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $application->documents = DB::table('visa_application_documents as vad')
                ->leftJoin('users as uploader', 'vad.uploaded_by', '=', 'uploader.id')
                ->leftJoin('users as reviewer', 'vad.reviewed_by', '=', 'reviewer.id')
                ->where('vad.visa_application_id', $applicationId)
                ->orderBy('vad.id', 'asc')
                ->select(
                    'vad.*',
                    'uploader.name as uploaded_by_name',
                    'reviewer.name as reviewed_by_name'
                )
                ->get();

            $application->status_logs = DB::table('visa_application_status_logs as vasl')
                ->leftJoin('users as changed_user', 'vasl.changed_by', '=', 'changed_user.id')
                ->where('vasl.visa_application_id', $applicationId)
                ->orderBy('vasl.id', 'asc')
                ->select(
                    'vasl.*',
                    'changed_user.name as changed_by_name'
                )
                ->get();

            if ($application->booking_id) {
                $application->payments = DB::table('payments as p')
                    ->leftJoin('transactions as t', 'p.id', '=', 't.payment_id')
                    ->where('p.booking_id', $application->booking_id)
                    ->orderBy('p.id', 'desc')
                    ->select(
                        'p.*',
                        't.id as transaction_id',
                        't.transaction_reference'
                    )
                    ->get();
            } else {
                $application->payments = [];
            }
        }

        return $application;
    }

    private function getPackagePayload($packageId, $activeOnly = false)
    {
        $query = DB::table('visa_packages as vp')
            ->join('visa_countries as vc', 'vp.visa_country_id', '=', 'vc.id')
            ->where('vp.id', $packageId)
            ->select(
                'vp.*',
                'vc.name as country_name',
                'vc.slug as country_slug',
                'vc.iso_code as country_iso_code'
            );

        if ($activeOnly) {
            $query->where('vp.is_active', 1)->where('vc.is_active', 1);
        }

        $package = $query->first();

        if ($package) {
            $package->required_documents = DB::table('visa_package_required_documents')
                ->where('visa_package_id', $packageId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        }

        return $package;
    }

    private function filterTransactionUpdateData($candidateData)
    {
        if (!Schema::hasTable('transactions')) {
            return [];
        }

        $columns = Schema::getColumnListing('transactions');

        return array_intersect_key($candidateData, array_flip($columns));
    }

    private function generateApplicationNo()
    {
        do {
            $applicationNo = 'VISA-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (DB::table('visa_applications')->where('application_no', $applicationNo)->exists());

        return $applicationNo;
    }

    private function buildFrontendUrl($path)
    {
        $frontendUrl = rtrim((string) env('FRONTEND_URL', ''), '/');

        if ($frontendUrl === '') {
            return '/' . ltrim($path, '/');
        }

        return $frontendUrl . '/' . ltrim($path, '/');
    }
}
