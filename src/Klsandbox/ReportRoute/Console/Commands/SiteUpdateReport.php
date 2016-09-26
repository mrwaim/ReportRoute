<?php

namespace Klsandbox\ReportRoute\Console\Commands;

use App\Models\Organization;
use App\Models\PaymentsApprovals;
use Klsandbox\ReportRoute\Models\MonthlyReport;
use Klsandbox\ReportRoute\Models\MonthlyUserReport;
use Klsandbox\ReportRoute\Services\ReportService;
use Auth;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SiteUpdateReport extends Command
{
    /**
     * @var ReportService $reportService
     */
    public $reportService;

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
    protected $name = 'site:updatereport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new report for a given month/year.';

    protected function getOptions()
    {
        return [
            ['year', null, InputOption::VALUE_REQUIRED, 'The report year.'],
            ['month', null, InputOption::VALUE_REQUIRED, 'The report month.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $month = $this->option('month');
        $year = $this->option('year');

        if (!$month || !$year) {
            $this->error('Month or year not set');

            return;
        }

        $report = MonthlyReport::where('month', '=', $month)
            ->where('year', '=', $year)->first();

        if (!$report) {
            $this->error('no report found');

            return;
        }

        $this->comment("updating monthly report : $report->id month:$month year:$year");

        $userClass = config('auth.model');

        Auth::setUser($userClass::admin());

        \DB::transaction(function () use ($report, $year, $month) {

            $userReports = MonthlyUserReport::where('monthly_report_id', '=', $report->id)
                ->get();

            $PaymentsApprovalsReports = PaymentsApprovals::where('monthly_report_id', '=', $report->id)
                ->get();

            foreach ($userReports as $userReport) {
                $this->comment("deleting old user monthly report : $userReport->id");
                $userReport->delete();
            }

            foreach ($PaymentsApprovalsReports as $paymentApprovalReport) {
                $this->comment("clearing out user payments approvals report : $paymentApprovalReport->id");
                $paymentApprovalReport->approved_state = 'not-reviewed';
                $paymentApprovalReport->save();
            }

            /**
             * @param $this
             * @param $year
             * @param $month
             * @param $is_hq
             * @param $organization_id
             * @param $report
             */
            function generateReport($self, $year, $month, $is_hq, $organization_id, $report)
            {
                $data = $self->reportService->getMonthlyReport($year, $month, $is_hq, $organization_id);
                $report = $report->fill([
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
                    'is_hq' => $is_hq,
                    'organization_id' => $organization_id,
                ]);

                $report->save();

                $self->comment("Report updated id:$report->id");
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
                        'online_payer' => (bool)$userData->onlinePayer,
                    ]);
                    $self->comment("  User Report created id:$userReport->id");
                }
            }

            generateReport($this, $year, $month, true, null, $report);

            foreach (Organization::all() as $organization) {
                generateReport($this, $year, $month, false, $organization->id, $report);
            }
        });
    }
}
