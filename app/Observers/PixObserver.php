<?php

namespace App\Observers;

use App\Interfaces\PixInterface;

class PixObserver
{
    public function __construct(PixInterface $pix){
        $this->pix = $pix;
    }

    public function created(){
        $this->pix->forgetCacheTag();
    }

    public function updated(){
        $this->pix->forgetCacheTag();
    }
    
    public function deleted(){
        $this->pix->forgetCacheTag();
    }
}
