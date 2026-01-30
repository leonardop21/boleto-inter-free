<?php

namespace Tests\Feature\Services;

use App\Exceptions\InterCertificateException;
use App\Services\InterService;
use App\Services\LnService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterServiceCertificateTest extends TestCase
{
    private function setEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    public function test_getSaldo_throws_when_certificate_paths_missing(): void
    {
        // Garante que não vamos bater em API externa mesmo se algo falhar
        Http::fake();

        $this->setEnv('INTER_PATH_CRT', '');
        $this->setEnv('INTER_PATH_KEY', '');

        $service = new InterService(new LnService());

        $this->expectException(InterCertificateException::class);
        $this->expectExceptionMessage('Certificado SSL do Banco Inter não foi configurado');

        $service->getSaldo();
    }
}

