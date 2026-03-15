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
        $monthArg = $this->argument('month');
        $yearArg = $this->argument('year');

        // If month is provided, generate for that specific month
        if ($monthArg) {
            $year = $yearArg ?? Carbon::now()->year;
            return $this->generateForMonth($monthArg, $year);
        }

        // Catch-up mode: Check for missing reports in the specified year or current year
        $year = $yearArg ?? Carbon::now()->year;
        
        // If checking current year, check up to the current month
        // If checking a past year, check all 12 months
        $isCurrentYear = ($year == Carbon::now()->year);
        $limitMonth = $isCurrentYear ? Carbon::now()->month : 12;

        if ($limitMonth < 1) {
            $this->info("No months to check.");
            return 0;
        }

        $generatedCount = 0;
        $this->info("Checking for missing monthly reports for year {$year} (up to " . Carbon::create($year, $limitMonth, 1)->format('F') . ")...");

        for ($m = 1; $m <= $limitMonth; $m++) {
            $reportMonthDate = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            
            $exists = MonthlyDailyBalanceReport::where('report_month', $reportMonthDate)->exists();

            if (!$exists) {
                $this->info("Month {$m} ({$year}) report is missing. Transitioning to generation...");
                $result = $this->generateForMonth($m, $year);
                if ($result === 0) {
                    $generatedCount++;
                }
            }
        }

        $this->info("Catch-up finished for year {$year}. Total reports generated: {$generatedCount}");
        return 0;
    }

    /**
     * Generate report for a specific month and year.
     * 
     * @param int $month
     * @param int $year
     * @return int
     */
    private function generateForMonth($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $monthName = $startDate->format('F');
        
        $this->info("Generating Daily Balance Report for {$monthName} {$year}...");

        try {
            // Fetch raw data from the database
            $rawReportData = $this->reportService->getDailyBalanceReportData($month, $year)->get();
            $dataByDate = $rawReportData->keyBy('date');

            $fullReportData = collect();
            $runningBalance = 0;

            // Loop through every day of the month
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currentDate = Carbon::create($year, $month, $d)->toDateString();
                
                if ($dataByDate->has($currentDate)) {
                    $dayData = (array) $dataByDate->get($currentDate);
                    // Update running balance based on actual totals
                    $runningBalance += ($dayData['total_credit'] - $dayData['total_debit']);
                    $dayData['balance'] = $runningBalance;
                    $fullReportData->push((object) $dayData);
                } else {
                    // Create a zeroed entry for missing dates
                    $fullReportData->push((object) [
                        'date' => $currentDate,
                        'tx_count' => 0,
                        'total_credit' => 0,
                        'total_debit' => 0,
                        'balance' => $runningBalance // Carry over previous balance
                    ]);
                }
            }

            // Calculate Summary Statistics
            $totalCredit = $fullReportData->sum('total_credit');
            $totalDebit = $fullReportData->sum('total_debit');
            $finalBalance = $runningBalance;

            // Prepare Chart Data (QuickChart)
            $labels = $fullReportData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d');
            })->toArray();
            $balanceData = $fullReportData->pluck('balance')->toArray();

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
                'reportData' => $fullReportData,
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
                'report_month' => Carbon::create($year, $month, 1)->startOfMonth()->toDateString(),
            ]);

            $this->info("Report generated successfully: {$filePath}");
            Log::info("Daily Balance PDF generated for {$monthName} {$year}");

        } catch (\Exception $e) {
            $this->error("Failed to generate report for {$monthName} {$year}: " . $e->getMessage());
            Log::error("Failed to generate Daily Balance PDF for {$monthName} {$year}: " . $e->getMessage());
            return 1;
        }

        return 0;
    }


}
