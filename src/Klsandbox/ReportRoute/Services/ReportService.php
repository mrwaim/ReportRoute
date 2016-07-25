<?php

namespace Klsandbox\ReportRoute\Services;

use App\Models\Order;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use Klsandbox\BillplzRoute\Models\BillplzResponse;
use Klsandbox\BonusModel\Models\BonusCurrency;
use Klsandbox\BonusModel\Models\BonusStatus;
use Klsandbox\OrderModel\Models\OrderStatus;
use Klsandbox\RoleModel\Role;

class ReportService
{
    private function getMonthlyCount(Collection $collection, Carbon $startDate)
    {
        $groups = $collection->groupBy(function ($e) {
            return $e->created_at->startOfMonth()->timestamp;
        });

        $list = [];
        for ($i = 12; $i >= 0; --$i) {
            $start = new Carbon();
            $start->startOfMonth();
            $start->addMonths(-$i);
            $end = new Carbon();
            $end->addMonths(-$i);
            $end->endOfMonth();

            $count = 0;
            foreach ($groups->keys() as $timestamp) {
                if ($timestamp >= $start->timestamp && $timestamp < $end->timestamp) {
                    $count += $groups->get($timestamp)->count();
                }
            }

//            $itemsInMonth = $collection->filter(function ($item) use ($start, $end) {
//                return $item->created_at >= $start && $item->created_at <= $end;
//            });
//
            if ($startDate->lte($start)) {
                array_push($list, $count);
            }
        }

        return $list;
    }

    public function getShortMonthName($startDate)
    {
        if ($startDate instanceof Carbon) {
        } else {
            $startDate = new Carbon($startDate);
        }

        $startDate->startOfMonth();

        $list = [];
        for ($i = 12; $i >= 0; --$i) {
            $start = new Carbon();
            $start->startOfMonth();
            $start->addMonths(-$i);

            if ($startDate->lte($start)) {
                array_push($list, $start->format('M'));
            }
        }

        return $list;
    }

    public function getMonthlyOrderCount($startDate)
    {
        if ($startDate instanceof Carbon) {
        } else {
            $startDate = new Carbon($startDate);
        }

        $startDate->startOfMonth();

        $q = Order::forSite()
            ->whereIn('order_status_id', [OrderStatus::Approved()->id, OrderStatus::Received()->id, OrderStatus::Shipped()->id]);

        if (Auth::user()->role->name != 'admin') {
            $q = $q->where('user_id', '=', Auth::user()->id);
        }

        $orders = $q->select('created_at')->get();

        return $this->getMonthlyCount($orders, $startDate);
    }

    public function getMonthlyNewUserCount($startDate)
    {
        if ($startDate instanceof Carbon) {
        } else {
            $startDate = new Carbon($startDate);
        }

        $startDate->startOfMonth();

        $q = User::forSite()
            ->where('new_user', '=', 1)
            ->where('account_status', '=', 'approved');

        if (Auth::user()->role->name != 'admin') {
            $q = $q->where(function ($qq) {
                $qq->where('referral_id', '=', Auth::user()->id)
                    ->orWhere('new_referral_id', '=', Auth::user()->id);
            });
        }

        $users = $q
            ->select('created_at')
            ->get();

        return $this->getMonthlyCount($users, $startDate);
    }

    public function getTopIntroducer($is_hq, $organizationId)
    {
        $g = User::forSite()
            ->where('account_status', '=', 'approved')
            ->where('role_id', '<>', User::admin()->role_id)
            ->groupBy('referral_id')
            ->where('referral_id', '<>', User::admin()->id)
            ->orderBy(DB::raw('count(*)'), 'DESC')
            ->get(['referral_id', DB::raw('count(*) as count')]);

        if (! $is_hq) {
            $g = $g->where('organization_id', $organizationId);
        }

        return $g->first();
    }

    public function getTotalStockist()
    {
        $count = User::forSite()
            ->where('account_status', '=', 'approved')
            ->where('role_id', '<>', User::admin()->role_id)
            ->count();

        return $count;
    }

    public function getTotalOrders()
    {
        $q = Order::forSite();
        $q = Order::whereApproved($q);
        $count = $q->count();

        return $count;
    }

    public function getCurrentMonthRevenue()
    {
        $q = Order::forSite();
        $q = Order::whereApproved($q);
        $q = $q->with('proofOfTransfer');
        $q = $q->where('created_at', '>=', ((new Carbon())->startOfMonth()));

        if ($q->count() > 0) {
            $total = 0;
            foreach ($q->get() as $order) {
                if ($order->proofOfTransfer) {
                    $total += $order->proofOfTransfer->amount;
                }
            }

            return $total;
        } else {
            return 0;
        }
    }

    // TODO: Record price for each
    public function getTotalRevenue()
    {
        $q = Order::forSite();
        $q = Order::whereApproved($q)
            ->with('proofOfTransfer')
            ->has('proofOfTransfer');

        $revenue = $q->get()->sum(function ($e) {
            return $e->proofOfTransfer->amount;
        });

        return $revenue;
    }

    public function getTopBonusUser()
    {
        $bonusList = $this->getTotalBonusPayoutPerPerson();

        $sorted = array_sort($bonusList, function ($e) {
            return $e['total'];
        });

        $best = last($sorted);

        $best['user'] = User::find($best['user_id']);

        return $best;
    }

    public function getTotalBonusPayoutPerPerson()
    {
        if (!config('bonus')) {
            return [];
        }

        $bonusClass = config('bonus.bonus_model');

        $g = $bonusClass::forSite()
            ->where('bonus_status_id', '=', BonusStatus::Active()->id)
            ->with(['bonusPayout', 'bonusPayout.bonusCurrency'])
            ->get();

        $g = $g->groupBy(function ($item) {
            return $item->awarded_to_user_id;
        });

        $list = [];
        foreach ($g->keys() as $key) {
            $payoutPerPerson = $this->getTotalBonusPayoutForList(collect($g->get($key)));

            $obj = (object)$payoutPerPerson;

            $payoutPerPerson['user_id'] = $key;
            $payoutPerPerson['total'] = $obj->cash + $obj->gold * 150 + $obj->bonusNotChosen * 150;
            array_push($list, $payoutPerPerson);
        }

        return $list;
    }

    public function getTotalBonusPayout()
    {
        if (!config('bonus')) {
            return [];
        }

        $bonusClass = config('bonus.bonus_model');

        $g = $bonusClass::forSite()
            ->where('bonus_status_id', '=', BonusStatus::Active()->id)
            ->with(['bonusPayout', 'bonusPayout.bonusCurrency'])
            ->get();

        return $this->getTotalBonusPayoutForList($g);
    }

    public function getBonusThisMonth($userId = false)
    {
        if ($userId == false) {
            $userId = \Auth::user()->id;
        }

        if (!config('bonus')) {
            return [];
        }

        $thisMonth = (new \Carbon\Carbon())->startOfMonth();

        $bonusClass = config('bonus.bonus_model');

        $g = $bonusClass::forSite()
            ->where('bonus_status_id', '=', BonusStatus::Active()->id)
            ->where('awarded_to_user_id', '=', $userId)
            // Need to resolve this, its so broken
//            ->where(DB::raw('MONTH(created_at)'), '=', $thisMonth)
            ->with(['bonusPayout', 'bonusPayout.bonusCurrency'])
            ->get()
            ->filter(function ($e) use ($thisMonth) {
                return $e->created_at->startOfMonth() == $thisMonth;
            });

        return $this->getTotalBonusPayoutForList($g);
    }

    private function getTotalBonusPayoutForList(Collection $bonusList)
    {
        $gr = $bonusList->groupBy(function ($e) {
            if (!$e->bonusPayout) {
                return 'bonusNotChosen';
            }

            return $e->bonusPayout->bonusCurrency->key;
        });

        $totals = ['cash' => 0, 'gold' => 0, 'bonusNotChosen' => 0];

        foreach ($gr->keys() as $key) {
            $currency = $gr->get($key)->map(function ($e) {
                if (!$e->bonusPayout) {
                    return 1;
                }

                return $e->bonusPayout->currency_amount;
            });

            $sum = $currency->sum();
            $totals[$key] = $sum;
        }

        return $totals;
    }

    public function totalRestock(User $user)
    {
        $q = $user->orders();
        $q = Order::whereApproved($q);
        $count = $q->count();

        return $count;
    }

    public function totalRevenue(User $user)
    {
        $q = $user->orders();
        $q = Order::whereApproved($q)
            ->with('proofOfTransfer')
            ->has('proofOfTransfer');

        $revenue = $q->get()->sum(function ($e) {
            return $e->proofOfTransfer->amount;
        });

        return $revenue;
    }

    public function totalDownline(User $user)
    {
        $q = $user->downLevels();
        $q = User::whereApproved($q);
        $count = $q->count();

        return $count;
    }

    public function monthlyBonusTotal(User $user)
    {
        $month = new Carbon();
        $month->startOfMonth();

        $group = $user->bonuses()->with('bonusPayout')->get()->filter(function ($bonus) use ($month) {
            if ($bonus->created_at->lt($month)) {
                return false;
            }

            if (!$bonus->bonusPayout) {
                return true;
            }

            if ($bonus->bonusPayout->getAttribute('hidden') == 1) {
                return false;
            }

            return true;
        })->groupBy(function ($bonus) {
            if (!$bonus->bonusPayout) {
                return 'bonus-not-chosen';
            }

            return $bonus->bonusPayout->bonusCurrency->key;
        });

        $list = [];

        foreach ($group->all() as $g) {
            $item = ['total' => 0, 'currency' => null];
            foreach ($g as $e) {
                if (!$e->bonusPayout) {
                    $item['currency'] = BonusCurrency::BonusNotChosen();
                    $item['total'] += 1;
                } else {
                    $item['currency'] = $e->bonusPayout->bonusCurrency;
                    $item['total'] = $e->bonusPayout->currency_amount;
                }
            }

            array_push($list, $item);
        }

        return $list;
    }

    private function getReportForUser(
        Carbon $date,
        Carbon $end,
        $user,
        Collection $allApprovedOrders,
        Collection $allUsers,
        Collection $bonusForMonth,
        $is_hq)
    {
        $data = ['user' => null, 'totalApprovedOrders' => 0, 'totalIntroductions' => 0, 'totalStockists' => 0, 'totalBonus' => null, 'bonusPayoutForMonth' => null, 'bonusIds' => null];
        $data = (object)$data;
        $data->user = $user;

        $userApprovedOrders = $allApprovedOrders->filter(function ($order) use ($user) {
            return $order->user_id == $user->id;
        });

        $data->totalApprovedOrders = $userApprovedOrders->count();

        $allDownLevelUsers = $allUsers->filter(function ($downLevel) use ($user, $is_hq) {
            if ($is_hq) {
                return $user->id == $downLevel->referral_id;
            } else {
                return $user->id == $downLevel->new_referral_id;
            }
        });

        $data->totalStockists = $allDownLevelUsers->count();

        $newDownLevelUsers = $allDownLevelUsers->filter(function ($downLevel) use ($date, $end) {
            return $downLevel->created_at >= $date && $downLevel->created_at <= $end;
        });

        $data->totalIntroductions = $newDownLevelUsers->count();

        $bonusForMonthUsers = $bonusForMonth->filter(function ($bonus) use ($user) {
            return $bonus->awarded_to_user_id == $user->id;
        });

        $data->onlinePayer = BillplzResponse::getCountUserPay($user->id, $date, $end);

        $bonusPayoutForMonth = (object)$this->getTotalBonusPayoutForList($bonusForMonthUsers);

        $data->bonusPayoutForMonth = $bonusPayoutForMonth;

        $data->bonusIds = $bonusForMonthUsers->pluck('id')->values();

        return $data;
    }

    public function getMonthlyReport($year, $month, $is_hq, $organization_id = null)
    {
        $userClass = config('auth.model');

        $date = new Carbon("$year-$month-01");
        $end = new Carbon("$year-$month-01");
        $end->endOfMonth();

        // Total orders made for the month
        // Total orders approved for the month
        // Total users joined for the month
        // Total users in system
        // Total revenue - sum(price) for the month
        // Total bonus paid out (Cash, Gold, Unchosen) for the month

        $allOrders = Order::forSite()
            ->with('user')
            ->where('created_at', '>=', $date)
            ->where('created_at', '<=', $end)
            ->orderBy('created_at')
            ->get();

        $allUsers = $userClass::forSite()
            ->where('created_at', '<=', $end)
            ->where('account_status', '=', 'Approved')
            ->get();

        $allUsers = $allUsers->filter(function ($user) use ($is_hq, $organization_id) {
            if ($is_hq) {
                return $user->referral_id;
            } else {
                return $user->organization_id == $organization_id;
            }
        });

        $allOrders = $allOrders->filter(function ($order) use ($is_hq, $organization_id) {
            return
                ($order->is_hq == $is_hq)
                && ($is_hq || $order->organization_id == $organization_id);
        });

        $totalOrders = $allOrders->count();

        $allApprovedOrders = $allOrders->filter(function ($order) use ($is_hq, $organization_id) {
            return
                (bool)$order->approved_at
                && $order->isApproved()
                && $order->proofOfTransfer != null;
        });

        $totalApprovedOrders = $allApprovedOrders->count();

        $newUsers = $allUsers->filter(function ($user) use ($date, $end) {
            return $user->new_user &&
            $user->created_at >= $date;
        });

        $newUsersCount = $newUsers->count();

        $totalUsersCount = $allUsers->count();

        $totalRevenue = $allApprovedOrders->sum(function ($e) {
            return $e->proofOfTransfer->amount;
        });

        $bonusForMonth = new Collection();
        $bonusPayoutForMonth = (object)['cash' => 0, 'gold' => 0, 'bonusNotChosen' => 0];

        if (config('bonus')) {
            $bonusClass = config('bonus.bonus_model');

            $bonusForMonth = $bonusClass::forSite()
                ->with(['bonusPayout', 'bonusPayout.bonusCurrency'])
                ->where('created_at', '>=', $date)
                ->where('created_at', '<=', $end)
                ->where('bonus_status_id', '=', BonusStatus::Active()->id);

            if ($organization_id) {
                $bonusForMonth = $bonusForMonth->where('awarded_by_organization_id', '=', $organization_id);
            }

            $bonusForMonth = $bonusForMonth->get();

            $bonusForMonth->load('orderItem.productPricing.product');
            $bonusForMonth = $bonusForMonth->filter(function ($bonus) use ($is_hq) {
                return $bonus->orderItem->productPricing->product->is_hq == $is_hq;
            });

            $bonusPayoutForMonth = (object)$this->getTotalBonusPayoutForList($bonusForMonth);
        }

        $userData = [];

        foreach ($allUsers as $user) {
            $userReport = $this->getReportForUser($date, $end, $user, $allApprovedOrders, $allUsers, $bonusForMonth, $is_hq, $organization_id);
            array_push($userData, $userReport);
        }

        $data = ['year' => $year, 'month' => $month, 'totalOrders' => $totalOrders, 'totalApprovedOrders' => $totalApprovedOrders, 'newUsersCount' => $newUsersCount, 'totalUsersCount' => $totalUsersCount, 'totalRevenue' => $totalRevenue, 'bonusPayoutForMonth' => $bonusPayoutForMonth, 'userData' => $userData,
        ];

        return (object)$data;
    }
}
