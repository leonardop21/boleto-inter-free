<?php

namespace App\Services;

class NfeService

{
    private $lnService;

    public function __construct(LnService $lnService){
        $this->lnService = $lnService;
    }

    public function sendNfe($archive){
        $url = env('NFE_BASE_URL');
        return $this->lnService->postNfe($url, $archive);
    }
}