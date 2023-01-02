<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $table = "orders";
    protected $with = ['purchaser', 'items'];
    protected $appends = ['order_total', 'percentage', 'commission', 'date', 'ref_count'];
    use HasFactory;


    public function purchaser()
    {
        return $this->belongsTo(User::class, "purchaser_id", "id");
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, "order_id", "id");
    }

    public function getOrderTotalAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += ($item->quantity * $item?->product->price);
        }
        return $total;
    }

    public function getPercentageAttribute()
    {
        $percent = $this->getPercentage();
        return $percent . '%';
    }

    public function getCommissionAttribute()
    {
        $total = $this->getOrderTotalAttribute();
        $percent = $this->getPercentage() / 100;
        return number_format($total * $percent, 2, '.', '');
    }

    public function getDateAttribute()
    {
        $date = Carbon::parse($this->order_date);
        return $date->format('m/d/Y');
    }

    public function getReferrer()
    {
        if ($this->distributor) {
            return $this->distributor;
        }
        $purchaser = $this->purchaser;
        return optional($purchaser->referredBy);
    }

    public function getReferredDistributors()
    {
        return collect($this->getReferrer()->referrals)->filter(function ($value, $key) {
            $isDistributor =  $value->is_distributor;
            $orderDate = Carbon::parse($this->order_date);
            $refDate = Carbon::parse($value->enrolled_date);
            return $isDistributor && $refDate->lt($orderDate);
        });
    }

    public function getRefCountAttribute()
    {
        return count($this->getReferredDistributors());
    }

    public function getPercentage()
    {
        $purchaser = $this->purchaser;
        if (!$purchaser->is_customer || !$this->getReferrer()->is_distributor) {
            return 0;
        }
        $count = $this->getRefCountAttribute();
        if ($count < 5) return 5;
        if ($count <= 10) return 10;
        if ($count <= 20) return 15;
        if ($count <= 30) return 20;
        return 30;
    }
}
