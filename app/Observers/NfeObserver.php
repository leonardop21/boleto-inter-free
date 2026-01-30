<?php

namespace App\Observers;

use App\Interfaces\NfeInterface;

class NfeObserver
{
    public function __construct(NfeInterface $nfe)
    {
        $this->nfe = $nfe;
    }

    public function created()
    {
        $this->nfe->forgetCacheTag();
    }

    public function updated()
    {
        $this->nfe->forgetCacheTag();
    }

    public function deleted()
    {
        $this->nfe->forgetCacheTag();
    }
}

