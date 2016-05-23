<?php

namespace Klsandbox\ReportRoute\Console\Commands;

use Klsandbox\ReportRoute\Models\MonthlyReport;
use Klsandbox\ReportRoute\Models\MonthlyUserReport;
use App\Models\PaymentsApprovals;
use Illuminate\Console\Command;
use Klsandbox\SiteModel\Site;

class SiteDeleteReport extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'site:deletereport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete report for specified month.';

    protected function getArguments()
    {
        return [['unused-key' => 'year'], ['unused-key2' => 'month']];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $month = $this->argument('month');
        $year = $this->argument('year');

        if (!$month || !$year) {
            $this->error('Month or year not set');

            return;
        }

        $this->comment('Deleting report for site ' . Site::key() . " year $year month $month");

        $report = MonthlyReport::forSite()
            ->where('month', '=', $month)
            ->where('year', '=', $year)->first();

        if (!$report) {
            $this->error('no report found');

            return;
        }
        $this->comment("deleting monthly report : $report->id");

        $userReports = MonthlyUserReport::forSite()
            ->where('monthly_report_id', '=', $report->id)
            ->get();

        $PaymentsApprovalsReports = PaymentsApprovals::forSite()->where('monthly_report_id', '=', $report->id)
            ->get();

        foreach ($userReports as $userReport) {
            $this->comment("deleting user monthly report : $userReport->id");
            $userReport->delete();
        }

        foreach ($PaymentsApprovalsReports as $userReport) {
            $this->comment("deleting user payments approvals report : $userReport->id");
            $userReport->delete();
        }

        $report->delete();
    }
}
