<?php

namespace Klsandbox\ReportRoute\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * Klsandbox\ReportRoute\Models\MonthlyUserReport
 *
 * @property integer $site_id
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property integer $monthly_report_id
 * @property integer $user_id
 * @property integer $orders_count
 * @property integer $introductions_count
 * @property integer $total_stockist_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereSiteId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereMonthlyReportId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereOrdersCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereIntroductionsCount($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereTotalStockistCount($value)
 * @property integer $bonus_payout_cash
 * @property integer $bonus_payout_gold
 * @property integer $bonus_payout_not_chosen
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereBonusPayoutCash($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereBonusPayoutGold($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereBonusPayoutNotChosen($value)
 * @property boolean $online_payer
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\ReportRoute\Models\MonthlyUserReport whereOnlinePayer($value)
 * @mixin \Eloquent
 */
class MonthlyUserReport extends Model
{
    use \Klsandbox\SiteModel\SiteExtensions;

    protected $guarded = [];

    //

    public static function boot()
    {
        parent::boot();

        self::created(function ($item) {
            Log::info("created\t#monthly_user_report:$item->id user:$item->user_id month:$item->month year:$item->year");
        });
    }

    public function user()
    {
        return $this->belongsTo(config('auth.model'));
    }

    public function monthlyReport()
    {
        return $this->belongsTo(MonthlyReport::class);
    }
}
