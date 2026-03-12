<?php

namespace App\Console\Commands;

use App\Models\MonthlyDailyBalanceReport;
use App\Repository\Services\Reports\ReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GenerateDailyBalancePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate-daily-balance-pdf {month?} {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a PDF report for daily balance and store it in the database.';

    protected $reportService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->argument('month') ?? Carbon::now()->month;
        $year = $this->argument('year') ?? Carbon::now()->year;

        $monthName = Carbon::create($year, $month, 1)->format('F');
        $this->info("Generating Daily Balance Report for {$monthName} {$year}...");

        try {
            $reportData = $this->reportService->getDailyBalanceReportData($month, $year)->get();

            if ($reportData->isEmpty()) {
                $this->warn("No data found for the specified month and year.");
                return 0;
            }

            // Calculate Summary Statistics
            $totalCredit = $reportData->sum('total_credit');
            $totalDebit = $reportData->sum('total_debit');
            $finalBalance = $reportData->last()->balance ?? 0;

            // Prepare Chart Data (QuickChart)
            $labels = $reportData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d');
            })->toArray();
            $balanceData = $reportData->pluck('balance')->toArray();

            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Daily Balance',
                        'data' => $balanceData,
                        'fill' => false,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'lineTension' => 0.1
                    ]]
                ],
                'options' => [
                    'title' => [
                        'display' => true,
                        'text' => "Balance Trend - {$monthName} {$year}"
                    ]
                ]
            ];
            $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));

            $pdf = PDF::loadView('reports.daily_balance_pdf', [
                'reportData' => $reportData,
                'monthName' => $monthName,
                'year' => $year,
                'totalCredit' => $totalCredit,
                'totalDebit' => $totalDebit,
                'finalBalance' => $finalBalance,
                'chartUrl' => $chartUrl
            ]);

            $fileName = "daily_balance_report_{$year}_{$month}_" . time() . ".pdf";
            $filePath = "reports/{$fileName}";

            Storage::disk('public')->put($filePath, $pdf->output());

            MonthlyDailyBalanceReport::create([
                'report_name' => "Daily Balance Report - {$monthName} {$year}",
                'file_path' => $filePath,
                'report_month' => Carbon::create($year, $month, 1)->toDateString(),
            ]);

            $this->info("Report generated successfully: {$filePath}");
            Log::info("Daily Balance PDF generated for {$monthName} {$year}");

        } catch (\Exception $e) {
            $this->error("Failed to generate report: " . $e->getMessage());
            Log::error("Failed to generate Daily Balance PDF: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
