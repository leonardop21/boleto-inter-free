<?php

namespace App\Models;

use App\Models\Boleto;
use App\Models\Service;
use App\Models\Nfe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $guarded = [];

    public function services(){
        return $this->belongsToMany(Service::class);
    }

    public function boletos(){
        return $this->hasMany(Boleto::class);
    }
    
    public function nfe(){
        return $this->hasMany(Nfe::class);
    }

    public function customerNfeType(){
        return $this->belongsTo(CustomerNfeType::class, 'customer_nfe_type_id');
    }

    public function pixes(){
        return $this->hasMany(Pix::class);
    }
}
