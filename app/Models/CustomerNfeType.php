<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNfeType extends Model
{
    use HasFactory;

    protected $table = 'customer_nfe_types';
    protected $guarded = [];
}
