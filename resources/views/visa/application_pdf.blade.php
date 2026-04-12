<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visa Application</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 24px;
        }

        h1, h2 {
            margin: 0 0 8px;
        }

        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 14px;
            margin-top: 18px;
            padding-bottom: 6px;
            border-bottom: 1px solid #d1d5db;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        .meta {
            margin-top: 12px;
        }

        .meta p {
            margin: 4px 0;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Visa Application Summary</h1>

    <div class="meta">
        <p><strong>Applicant:</strong> {{ $application->user_name }}</p>
        <p><strong>Email:</strong> {{ $application->user_email }}</p>
        <p><strong>Country:</strong> {{ $application->country_name }}</p>
        <p><strong>Visa Type:</strong> {{ $application->visa_name }}</p>
        <p><strong>Status:</strong> {{ $application->status }}</p>
        <p><strong>Assigned Officer:</strong> {{ $application->assigned_officer_name ?? 'Not assigned' }}</p>
        <p><strong>Applied At:</strong> {{ $application->applied_at ?? 'Not submitted yet' }}</p>
        <p><strong>Processed At:</strong> {{ $application->processed_at ?? 'Not processed yet' }}</p>
    </div>

    <h2>Application</h2>
    <table>
        <tr>
            <th>Visa Application ID</th>
            <td>{{ $application->id }}</td>
            <th>Booking ID</th>
            <td>{{ $application->booking_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Package Booking ID</th>
            <td>{{ $application->package_booking_id ?? 'N/A' }}</td>
            <th>Remarks</th>
            <td>{{ $application->remarks ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Processing Days</th>
            <td>{{ $application->processing_days ?? 'N/A' }}</td>
            <th>Fee</th>
            <td>{{ $application->fee ?? 'N/A' }}</td>
        </tr>
    </table>

    <h2>Applicant Info</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <td>{{ optional($application->applicant_info)->full_name ?? 'N/A' }}</td>
            <th>Passport Number</th>
            <td>{{ optional($application->applicant_info)->passport_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Passport Expiry</th>
            <td>{{ optional($application->applicant_info)->passport_expiry ?? 'N/A' }}</td>
            <th>Date of Birth</th>
            <td>{{ optional($application->applicant_info)->date_of_birth ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Nationality</th>
            <td>{{ optional($application->applicant_info)->nationality ?? 'N/A' }}</td>
            <th>Phone</th>
            <td>{{ optional($application->applicant_info)->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ optional($application->applicant_info)->email ?? 'N/A' }}</td>
            <th>Address</th>
            <td>{{ optional($application->applicant_info)->address ?? 'N/A' }}</td>
        </tr>
    </table>

    <h2>Documents</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->documents as $document)
                <tr>
                    <td>{{ $document->document_type }}</td>
                    <td>{{ $document->status }}</td>
                    <td>{{ $document->remarks ?? 'N/A' }}</td>
                    <td>{{ $document->uploaded_at ?? $document->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No documents uploaded</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Status Logs</h2>
    <table>
        <thead>
            <tr>
                <th>Old Status</th>
                <th>New Status</th>
                <th>Changed By</th>
                <th>Remarks</th>
                <th>Changed At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->status_logs as $log)
                <tr>
                    <td>{{ $log->old_status ?? 'N/A' }}</td>
                    <td>{{ $log->new_status ?? 'N/A' }}</td>
                    <td>{{ $log->changed_by_name ?? 'System' }}</td>
                    <td>{{ $log->remarks ?? 'N/A' }}</td>
                    <td>{{ $log->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No status logs found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
