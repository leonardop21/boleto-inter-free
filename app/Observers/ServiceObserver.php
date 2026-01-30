<?php

namespace App\Observers;

use App\Interfaces\ServiceInterface;
use Illuminate\Support\Facades\Cache;

class ServiceObserver
{
    public function __construct(ServiceInterface $service){
        $this->service = $service;
    }

    public function created(){
        $this->service->forgetCacheTag();
        // Cache do dashboard (previsão total dos serviços) não usa tags
        Cache::forget('previsao_valor_total_servicos');
    }
    public function updated(){
        $this->service->forgetCacheTag();
        // Cache do dashboard (previsão total dos serviços) não usa tags
        Cache::forget('previsao_valor_total_servicos');
    }
    public function deleted(){
        $this->service->forgetCacheTag();
        // Cache do dashboard (previsão total dos serviços) não usa tags
        Cache::forget('previsao_valor_total_servicos');
    }
}
