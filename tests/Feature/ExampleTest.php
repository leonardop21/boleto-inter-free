<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        // Em produção esse endpoint normalmente redireciona (login/dashboard).
        // O objetivo aqui é só garantir que a rota existe e responde.
        $response = $this->get('/');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 301, 302], true));
    }
}
