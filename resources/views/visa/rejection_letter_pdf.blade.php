<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rejection Letter</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #1f2937;
            line-height: 1.7;
            margin: 32px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .meta {
            margin-bottom: 24px;
        }

        .meta p {
            margin: 4px 0;
        }

        .box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 18px;
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Visa Rejection Letter</h1>

    <div class="meta">
        <p><strong>Application No:</strong> {{ $application->application_no }}</p>
        <p><strong>Applicant:</strong> {{ $application->full_name }}</p>
        <p><strong>Country:</strong> {{ $application->country_name }}</p>
        <p><strong>Visa Type:</strong> {{ $application->visa_type_snapshot }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
    </div>

    <p>Dear {{ $application->full_name }},</p>

    <p>
        We regret to inform you that your visa application for {{ $application->country_name }}
        has been rejected after review.
    </p>

    <div class="box">
        <strong>Reason for rejection</strong>
        <p>{{ $application->rejection_reason }}</p>
    </div>

    <p>
        If you believe this decision was made due to missing or incorrect documents,
        you may contact the support team or submit a fresh application with updated information.
    </p>

    <p>Sincerely,</p>
    <p>Visa Processing Team</p>
</body>
</html>
