<?php

namespace Klsandbox\ReportRoute\Console\Commands;

use Klsandbox\ReportRoute\Models\MonthlyReport;
use Klsandbox\ReportRoute\Models\MonthlyUserReport;
use Klsandbox\ReportRoute\Services\ReportService;
use Auth;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Klsandbox\SiteModel\Site;

class SiteMakeReport extends Command
{
    /**
     * @var ReportService $reportService
     */
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        parent::__construct();
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'site:makereport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate report for months with no reports, and month is complete.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $userClass = config('auth.model');

        $boot = new MonthlyReport();

        $this->comment('Generating reports for site ' . Site::key());

        Auth::setUser($userClass::admin());

        \DB::transaction(function () {
            foreach ($this->getLastThreeMonths() as $date) {
                $this->comment('Generating report for ' . $date);

                $report = MonthlyReport::where('site_id', '=', Site::id())
                    ->where('month', '=', $date->month)
                    ->where('year', '=', $date->year)
                    ->first();
                if ($report) {
                    $this->comment("Report exists id:$report->id skipping");
                    continue;
                }

                $data = $this->reportService->getMonthlyReport($date->year, $date->month);
                $report = MonthlyReport::create([
                    'year' => $data->year,
                    'month' => $data->month,
                    'orders_count' => $data->totalOrders,
                    'approved_orders_count' => $data->totalApprovedOrders,
                    'new_users_count' => $data->newUsersCount,
                    'total_users_count' => $data->totalUsersCount,
                    'total_revenue' => $data->totalRevenue,
                    'bonus_payout_cash' => $data->bonusPayoutForMonth->cash,
                    'bonus_payout_gold' => $data->bonusPayoutForMonth->gold,
                    'bonus_payout_not_chosen' => $data->bonusPayoutForMonth->bonusNotChosen,
                ]);

                $this->comment("Report created id:$report->id");
                foreach ($data->userData as $userData) {
                    $userReport = MonthlyUserReport::create([
                        'monthly_report_id' => $report->id,
                        'user_id' => $userData->user->id,
                        'orders_count' => $userData->totalApprovedOrders,
                        'introductions_count' => $userData->totalIntroductions,
                        'total_stockist_count' => $userData->totalStockists,
                        'bonus_payout_cash' => $userData->bonusPayoutForMonth->cash,
                        'bonus_payout_gold' => $userData->bonusPayoutForMonth->gold,
                        'bonus_payout_not_chosen' => $userData->bonusPayoutForMonth->bonusNotChosen,
                        'online_payer' => (bool) $userData->onlinePayer,
                    ]);
                    $this->comment("  User Report created id:$userReport->id");
                }
            }
        });
    }

    public function getLastThreeMonths()
    {
        $list = [];
        for ($i = 3; $i >= 0; --$i) {
            $start = new Carbon();
            $start->startOfMonth();
            $start->addMonths(-$i - 1);

            array_push($list, $start);
        }

        return $list;
    }

    public function getLastTwelveMonths()
    {
        $list = [];
        for ($i = 12; $i >= 0; --$i) {
            $start = new Carbon();
            $start->startOfMonth();
            $start->addMonths(-$i - 1);

            array_push($list, $start);
        }

        return $list;
    }
}
