<?php

namespace Klsandbox\ReportRoute\Models;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Klsandbox\BillplzRoute\Models\BillplzResponse;
use Klsandbox\NotificationService\Models\NotificationRequest;
use Klsandbox\TimelineEvents\TimelineEvent;
use Log;

/**
 * Klsandbox\ReportRoute\Models\MonthlyReport
 *
 * @property integer $site_id
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property integer $year
 * @property integer $month
 * @property integer $orders_count
 * @property integer $approved_orders_count
 * @property integer $new_users_count
 * @property integer $total_users_count
 * @property integer $total_revenue
 * @property integer $bonus_payout_cash
 * @property integer $bonus_payout_gold
 * @property integer $bonus_payout_not_chosen
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereSiteId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereYear($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereMonth($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereOrdersCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereApprovedOrdersCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereNewUsersCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereTotalUsersCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereTotalRevenue($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereBonusPayoutCash($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereBonusPayoutGold($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereBonusPayoutNotChosen($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MonthlyUserReport[] $userReports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PaymentsApprovals[] $userPaymentsApprovals
 * @mixin \Eloquent
 * @property boolean $is_hq
 * @property integer $organization_id
 * @property integer $admin_id
 * @property boolean $draft
 * @property-read \App\Models\Organization $organization
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereIsHq($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereOrganizationId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereAdminId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyReport whereDraft($value)
 */
class MonthlyReport extends Model
{
    protected $guarded = [];

    //
    public static function boot()
    {
        parent::boot();

        self::created(function ($item) {
            Log::info("created\t#monthly_report:$item->id month:$item->month year:$item->year");

            NotificationRequest::create(['target_id' => $item->id, 'route' => 'new-monthly-report', 'channel' => 'Sms', 'to_user_id' => User::admin()->id]);

            TimelineEvent::create(['created_at' => (new Carbon("$item->year-$item->month-01"))->endOfMonth(), 'controller' => 'timeline', 'user_id' => User::admin()->id, 'route' => '/new-monthly-report', 'target_id' => $item->id]);
        });
    }

    public function getBonusPayout()
    {
        return (object)['cash' => $this->bonus_payout_cash, 'gold' => $this->bonus_payout_gold, 'bonusNotChosen' => $this->bonus_payout_not_chosen];
    }

    public function userReports()
    {
        return $this->hasMany(MonthlyUserReport::class);
    }

    public function userPaymentsApprovals()
    {
        return $this->hasMany('App\Models\PaymentsApprovals');
    }

    public function getCarbon()
    {
        return new Carbon(date("$this->year-$this->month-01"));
    }

    /**
     * @param $is_hq
     * @param $organization_id
     * @return Builder
     */
    public static function forOrganization($is_hq, $organization_id)
    {
        $report = self::where('is_hq', '=', $is_hq);

        if ($organization_id) {
            $report = $report->where('organization_id', '=', $organization_id);
        }

        return $report;
    }

    public static function getBonusPaymentsList($is_hq, $organization_id)
    {
        $data = self::forOrganization($is_hq, $organization_id)
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get();

        /**
         * @var MonthlyReport $itm
         */
        foreach ($data as $key => $itm) {
            $online_users = [];
            $data[$key]['approve_online'] = $itm->userPaymentsApprovals()->where('approved_state', 'approve')->where('user_type', 'online')->count();
            $data[$key]['approve_manual'] = $itm->userPaymentsApprovals()->where('approved_state', 'approve')->where('user_type', 'manual')->count();
            $data[$key]['reject_online'] = $itm->userPaymentsApprovals()->where('approved_state', 'reject')->where('user_type', 'online')->count();
            $data[$key]['reject_manual'] = $itm->userPaymentsApprovals()->where('approved_state', 'reject')->where('user_type', 'manual')->count();

            $start_date = new Carbon(date("{$itm->year}-{$itm->month}-01"));
            $end_date = new Carbon(date("{$itm->year}-{$itm->month}-01"));
            $end_date->endOfMonth();

            $report = $itm->userReports()->getQuery();

            $online_users_data = BillplzResponse::select('metadata_user_id')
                ->where('created_at', '>=', $start_date)
                ->where('created_at', '<=', $end_date)
                ->where('paid', true)
                ->groupBy('metadata_user_id')
                ->get()->toArray();

            foreach ($online_users_data as $val) {
                $online_users[] = $val['metadata_user_id'];
            }

            $report = $report->where('bonus_payout_cash', '>', 0);

            $onlineReport = clone $report;
            $manualReport = clone $report;

            $online = (!empty($online_users)) ? $onlineReport->whereIn('user_id', $online_users)->count() : 0;
            $manual = (!empty($online_users)) ? $manualReport->whereNotIn('user_id', $online_users)->count() : $manualReport->count();

            $data[$key]['not_reviewed_online'] = $online - ($data[$key]['approve_online'] + $data[$key]['reject_online']);
            $data[$key]['not_reviewed_manual'] = $manual - ($data[$key]['approve_manual'] + $data[$key]['reject_manual']);

//            assert($data[$key]['not_reviewed_online'] >= 0);
//            assert($data[$key]['not_reviewed_manual'] >= 0);
        }

        return $data;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
