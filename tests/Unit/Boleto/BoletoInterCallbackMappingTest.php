<?php

namespace Tests\Unit\Boleto;

use App\Interfaces\NotifishInterface;
use App\Repositories\BoletoRepository;
use App\Services\InterService;
use Tests\TestCase;

class BoletoInterCallbackMappingTest extends TestCase
{
    private function repo(): BoletoRepository
    {
        return new BoletoRepository(
            \Mockery::mock(InterService::class),
            \Mockery::mock(NotifishInterface::class)
        );
    }

    public function test_maps_recebido_pix_as_pago(): void
    {
        $payload = [
            'situacao' => 'RECEBIDO',
            'origemRecebimento' => 'PIX',
            'dataHoraSituacao' => '2019-08-24T14:15:22Z',
            'valorNominal' => 1800.0,
            'valorTotalRecebido' => 1800.0,
        ];

        $data = $this->repo()->mapInterCallbackToBoletoUpdate($payload);

        $this->assertSame('PAGO', $data['status']);
        $this->assertSame('2019-08-24', $data['payment_date']);
        $this->assertSame(1800.0, $data['price_paid']);
        $this->assertArrayNotHasKey('cancellation_date', $data);
    }

    public function test_maps_pago_as_pago_and_uses_valorTotalRecebimento_old_field(): void
    {
        $payload = [
            'situacao' => 'PAGO',
            'origemRecebimento' => 'BOLETO',
            'dataHoraSituacao' => '2026-01-26',
            'valorNominal' => 1800.0,
            'valorTotalRecebimento' => 1800.0,
        ];

        $data = $this->repo()->mapInterCallbackToBoletoUpdate($payload);

        $this->assertSame('PAGO', $data['status']);
        $this->assertSame('2026-01-26', $data['payment_date']);
        $this->assertSame(1800.0, $data['price_paid']);
    }

    public function test_maps_cancelado_as_cancelado(): void
    {
        $payload = [
            'situacao' => 'CANCELADO',
            'dataHoraSituacao' => '2026-01-26',
        ];

        $data = $this->repo()->mapInterCallbackToBoletoUpdate($payload);

        $this->assertSame('CANCELADO', $data['status']);
        $this->assertSame('2026-01-26', $data['cancellation_date']);
        $this->assertArrayNotHasKey('payment_date', $data);
    }
}

