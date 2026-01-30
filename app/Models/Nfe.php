<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfe extends Model
{
    use HasFactory;

    protected $table = 'nfes';
    protected $guarded = [];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id')->with("services");
    }

    public function boleto(){
        return $this->belongsTo(Boleto::class, 'boleto_id');
    }
}
