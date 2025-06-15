<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'orders' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the orders as an array (fallback if casting doesn't work)
     */
    public function getOrdersArrayAttribute()
    {
        if (is_string($this->orders)) {
            return json_decode($this->orders, true) ?? [];
        }
       
        return $this->orders ?? [];
    }

    /**
     * Get the total number of orders
     */
    public function getOrdersCountAttribute()
    {
        return count($this->orders_array);
    }

    /**
     * Get the total price of all orders
     */
    public function getTotalPriceAttribute()
    {
        return collect($this->orders_array)->sum('price');
    }
}
