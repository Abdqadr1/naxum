<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $table = "order_items";
    use HasFactory;

    protected $with = ['product'];

    protected $appends = ['total'];

    public function order()
    {
        return $this->belongsTo(Order::class, "id", "order_id");
    }

    public function product()
    {
        return $this->hasOne(Product::class, "id", "product_id");
    }

    public function getTotalAttribute()
    {
        return number_format($this->quantity * $this->product->price, 2, '.', '');
    }
}
