<?php

namespace App\Interfaces;

interface PixInterface
{
    public function paginate();
    public function update($data, $id);
    public function storePix($customer, $data, $geracaoAutomtica);
    public function cancel($id);
    public function findByUuid($uuid);
    public function regeneratePixByCustomer($pixId);
    public function extractInterErrors($response);
    public function createWebhook();
    public function getWebhook();
    public function callbackInter($callback);
    public function withPaginate($with, $customer_id);
}