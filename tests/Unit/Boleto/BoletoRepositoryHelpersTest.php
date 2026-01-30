<?php

namespace Tests\Unit\Boleto;

use App\Interfaces\NotifishInterface;
use App\Repositories\BoletoRepository;
use App\Services\InterService;
use Tests\TestCase;

class BoletoRepositoryHelpersTest extends TestCase
{
    private function makeRepo(): BoletoRepository
    {
        $inter = \Mockery::mock(InterService::class);
        $notifish = \Mockery::mock(NotifishInterface::class);
        return new BoletoRepository($inter, $notifish);
    }

    public function test_extractInterErrors_with_violacoes_object(): void
    {
        $repo = $this->makeRepo();

        $response = (object) [
            'violacoes' => [
                (object) ['propriedade' => 'incluirCobrancaAsync.body.pagador.cep', 'razao' => 'Deve ter 8 caracteres'],
                (object) ['propriedade' => 'x.y', 'razao' => 'Inválido'],
            ],
        ];

        $errors = $repo->extractInterErrors($response);

        $this->assertSame([
            'incluirCobrancaAsync.body.pagador.cep Deve ter 8 caracteres',
            'x.y Inválido',
        ], $errors);
    }

    public function test_extractInterErrors_with_detail_array(): void
    {
        $repo = $this->makeRepo();

        $response = [
            'detail' => 'Verifique se os dados informados estão de acordo com a documentação.',
        ];

        $errors = $repo->extractInterErrors($response);
        $this->assertSame([$response['detail']], $errors);
    }

    public function test_adjusteDueDateBasedHoliday_returns_due_day_when_different(): void
    {
        $repo = $this->makeRepo();

        $customer = (object) ['expiration_day_boleto' => '10'];
        $data = ['due_date' => '2026-01-15'];

        $this->assertSame('15', $repo->adjusteDueDateBasedHoliday($customer, $data));
    }

    public function test_adjusteDueDateBasedHoliday_returns_false_when_same_day(): void
    {
        $repo = $this->makeRepo();

        $customer = (object) ['expiration_day_boleto' => '15'];
        $data = ['due_date' => '2026-01-15'];

        $this->assertFalse($repo->adjusteDueDateBasedHoliday($customer, $data));
    }

    public function test_checkDueDateMoraAndJuros_adds_one_month_when_expiration_greater_than_date(): void
    {
        $repo = $this->makeRepo();

        $yearMonth = date('Y-m-');
        $expected = date('Y-m-d', strtotime($yearMonth . '05 +1 month'));

        $this->assertSame($expected, $repo->checkDueDateMoraAndJuros(20, '05', false));
    }

    public function test_checkDueDateMoraAndJuros_adds_one_day_when_due_date_adjusted_by_holiday(): void
    {
        $repo = $this->makeRepo();

        $expected = date('Y-m-d', strtotime(date('Y-m-20') . ' +1 day'));
        $this->assertSame($expected, $repo->checkDueDateMoraAndJuros(10, '10', '20'));
    }
}

