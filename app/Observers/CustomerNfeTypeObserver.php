<?php

namespace App\Observers;

use App\Interfaces\CustomerNfeTypeInterface;

class CustomerNfeTypeObserver
{
    public function __construct(CustomerNfeTypeInterface $customerNfeType){
        $this->customerNfeType = $customerNfeType;
    }

    public function created(){
        $this->customerNfeType->forgetCacheTag();
    }

    public function updated(){
        $this->customerNfeType->forgetCacheTag();
    }
    
    public function deleted(){
        $this->customerNfeType->forgetCacheTag();
    }
}
