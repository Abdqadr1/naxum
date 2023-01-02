<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    protected $table = "users";
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['full_name', 'is_distributor', 'is_customer'];

    protected $relation_table = "user_category";


    protected $with = ['categories'];

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function allOrders()
    {
        return $this->hasManyThrough(Order::class, User::class, 'referred_by', 'purchaser_id', 'id', 'id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, $this->relation_table, 'user_id', 'category_id');
    }

    public function getFullNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }

    public function getIsDistributorAttribute()
    {
        return collect($this->categories)->contains(function ($value, $key) {
            return $value->name == "Distributor";
        });
    }

    public function getIsCustomerAttribute()
    {
        return collect($this->categories)->contains(function ($value, $key) {
            return $value->name == "Customer";
        });
    }
}
