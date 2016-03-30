<?php

namespace Klsandbox\ReportRoute\Http\Controllers;

use Klsandbox\ReportRoute\Models\MonthlyReport;
use Artisan;
use Session;
use Redirect;
use Klsandbox\SiteModel\Site;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function postUpdateMonthlyReport($year, $month)
    {
        Artisan::call('site:updatereport', ['--year' => $year, '--month' => $month]);
        Session::flash('success_message', 'Report has been refreshed.');
        return Redirect::back();
    }

    public function getMonthlyReport($year, $month, $filter)
    {
        $report = MonthlyReport::forSite()
            ->where('year', '=', $year)
            ->where('month', '=', $month)
            ->with('userReports')
            ->first();

        Site::protect($report, 'Report');

        $userReports = $report->userReports;

        if ($filter == 'active') {
            $userReports = $userReports->filter(function ($item) {
                return $item->orders_count +
                $item->introductions_count +
                $item->bonus_payout_cash +
                $item->bonus_payout_gold +
                $item->bonus_payout_not_chosen;
            });
        }

        $hasBonus = !!config('bonus');

        return view('report-route::monthly-report')
            ->with('year', $year)
            ->with('month', $month)
            ->with('totalOrders', $report->orders_count)
            ->with('totalApprovedOrders', $report->approved_orders_count)
            ->with('newUsersCount', $report->new_users_count)
            ->with('totalUsersCount', $report->total_users_count)
            ->with('totalRevenue', $report->total_revenue)
            ->with('bonusPayoutForMonth', $report->getBonusPayout())
            ->with('userData', $userReports)
            ->with('filter', $filter)
            ->with('has_bonus', $hasBonus);
    }

    public function getMonthlyReportList()
    {
        $list = MonthlyReport::forSite()
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->paginate(10);

        return view('report-route::list-monthly-report')->with('list', $list);
    }
}
