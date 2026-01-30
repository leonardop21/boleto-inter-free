<?php

namespace App\Interfaces;

interface BoletoInterface
{
    public function paginate();
    public function store($data);
    public function cancel($id);

    /**
     * Extrai mensagens de erro da resposta da API Inter.
     * @param mixed $response Resposta HTTP (Guzzle/Illuminate) ou string JSON
     * @return array Lista de mensagens de erro
     */
    public function extractInterErrors($response);
}
