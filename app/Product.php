<?php

namespace App;

use App\Seller;
use App\Product;
use App\Category;
use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const AVAILABLE_PRODUCT = 'available';
    const UNAVAILABLE_PRODUCT = 'unavailable';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'quantity', 'status', 'image', 'seller_id',
    ];

    public function isAvailable()
    {
        return $this->status == Product::AVAILABLE_PRODUCT;
    }

    public function seller()
    {
        return $this->belongsTo('App\Seller');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
    
    public function categories()
    {
        return $this->belongsToMany('App\Category');
    }
}
