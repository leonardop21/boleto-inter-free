<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\ProcessPdfReportAndSentEmailEWhatsApp;
use App\Interfaces\NotifishInterface;
use App\Services\InterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Http\Client\Response as HttpClientResponse;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Tests\TestCase;

class ProcessPdfReportAndSentEmailEWhatsAppTest extends TestCase
{
    public function test_command_generates_pdf_and_sends_via_notifish_without_calling_inter_sandbox(): void
    {
        // Avoid any real HTTP calls
        Http::fake();
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 28, 12, 0, 0, 'America/Sao_Paulo'));

        $_ENV['CONTABILIDADE_EMAIL'] = 'conta@test.com';
        $_ENV['CONTABILIDADE_WHATSAPP'] = '5511999999999';
        $_ENV['CONTABILIDADE_NAME'] = 'Contador';
        $_ENV['NOTIFISH_UUID'] = 'notifish-uuid';

        // InterService::generateReportExtractPdf is expected to return an HTTP client response
        $inter = \Mockery::mock(InterService::class);
        $inter->shouldReceive('generateReportExtractPdf')
            ->once()
            ->andReturn(new HttpClientResponse(
                new Psr7Response(200, ['Content-Type' => 'application/json'], json_encode(['pdf' => base64_encode('PDFDATA')]))
            ));

        $notifish = \Mockery::mock(NotifishInterface::class);
        $notifish->shouldReceive('sendMessage')->once();
        $notifish->shouldReceive('sendArchive')
            ->once()
            ->andReturn(['success' => true, 'status' => 'success']);

        // Test double: avoid DB write in saveDb()
        $cmd = new class($inter, $notifish) extends ProcessPdfReportAndSentEmailEWhatsApp {
            public array $saved = [];
            public function saveDb($data)
            {
                $this->saved = $data;
                return $data;
            }
        };
        $cmd->setLaravel($this->app);
        $cmd->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput()));

        $cmd->handle();

        // Arquivos gerados
        $year = 2025; // mês anterior (Dezembro/2025)
        $firstDay = '2025-12-01';
        $lastDay = '2025-12-31';
        $filename = "LN-SISTEMAS-extrato-inter-$firstDay-ate-$lastDay.pdf";

        $privateFile = storage_path('app/pdfs/contabilidade/' . $year . '/' . $filename);
        $publicFile = storage_path('app/public/contabilidade/' . $year . '/' . $filename);

        $this->assertFileExists($privateFile);
        $this->assertFileExists($publicFile);
        $this->assertSame('PDFDATA', file_get_contents($privateFile));
        $this->assertSame('PDFDATA', file_get_contents($publicFile));

        $this->assertSame('YES', $cmd->saved['envio_email']);
        $this->assertSame('YES', $cmd->saved['envio_whatsapp']);
    }
}

