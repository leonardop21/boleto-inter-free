<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pix extends Model {
    use HasFactory;

    protected $table = 'pixes';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // Defina um scope global para carregar automaticamente o relacionamento "customer"
        static::addGlobalScope('withCustomer', function ($builder) {
            $builder->with('customer');
        });
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id')->with("services");
    }


}