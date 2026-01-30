<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Contabilidade;
use App\Services\InterService;
use App\Interfaces\NotifishInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessPdfReportAndSentEmailEWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ln:process-pdf-report-and-sent-email-e-whats-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa o extrato do banco inter do mês anterior e envia para a contabilidade por email e WhatsApp';

    private $interService;
    private $notifish;
    private $whatsapp;

    public function __construct(InterService $interService, NotifishInterface $notifish)
    {
        parent::__construct();
        $this->interService = $interService;
        $this->notifish = $notifish;

        $this->whatsapp = env('CONTABILIDADE_WHATSAPP');
    }
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(){
        // Obter a data atual
        $this->info('PDF report - iniciando.');

        $currentDate = Carbon::now();

        // Definir o último dia do mês anterior
        $lastDayLastMonth = $currentDate->subMonth()->endOfMonth()->format('Y-m-d');

        // Definir o primeiro dia do mês anterior
        $firstDayLastMonth = Carbon::createFromFormat('Y-m-d', $lastDayLastMonth)->startOfMonth()->format('Y-m-d');

        // Obter o ano e o mês
        $data['year'] = Carbon::createFromFormat('Y-m-d', $firstDayLastMonth)->year;
        $data['month'] = Carbon::createFromFormat('Y-m-d', $firstDayLastMonth)->monthName;

        $data['email'] = env('CONTABILIDADE_EMAIL');

        $this->info('PDF report - Consulta e download banco inter.' . $firstDayLastMonth . ' - ' . $lastDayLastMonth);

        // Download do PDF e armazenamento no sistema de arquivos
        $response = $this->interService->generateReportExtractPdf($firstDayLastMonth, $lastDayLastMonth);
        $pdfBinary = base64_decode($response['pdf']);
        $filename = "LN-SISTEMAS-extrato-inter-$firstDayLastMonth-ate-$lastDayLastMonth.pdf";

        $this->info('PDF report - salvando.');

        // Arquivo privado (para anexar no e-mail)
        $privatePath = 'pdfs/contabilidade/' . $data['year'] . '/' . $filename;
        Storage::put($privatePath, $pdfBinary);

        // Envio por e-mail
        $file = storage_path('app/' . $privatePath);

        $this->info('PDF report - enviando por e-mail.');


        Mail::send('Emails.report-pdf-contabilidade', $data, function ($message) use ($data, $file) {
            $message->to($data['email'])->bcc(env('NOTIFY_SEND_BOLETO'))->subject("LN SISTEMAS - Extrato do Banco Inter - $data[month]/$data[year]");
            $message->attach($file);
        });

        $database['nome'] = env('CONTABILIDADE_NAME');
        $database['email'] = $data['email'];
        $database['whatsapp'] = $this->whatsapp;
        $database['envio_email'] = 'YES';
        $database['envio_email_em'] = Carbon::now();
        $database['arquivo_pdf'] = $file;
       

        $this->info('PDF report - enviando por whatsapp.');

        // Arquivo público (para a Notifish conseguir baixar via URL)
        $publicPath = 'contabilidade/' . $data['year'] . '/' . $filename;
        Storage::disk('public')->put($publicPath, $pdfBinary);
        $publicUrl = asset('storage/' . $publicPath);

        $string = "*LN SISTEMAS - Extrato do Banco Inter* \n\n
        *Olá, Contador Direto!*
        Segue em anexo o extrato financeiro referente ao mês de {$data['month']} de {$data['year']}. \n\n
        Mensagem enviada automaticamente, em caso de dúvidas, favor entrar em contato via WhatsApp (45) 9 9959-5186 ou e-mail leonardo.nascimento21@gmail.com
        \n\n
        *Mensagem enviada automaticamente em* " . date('d/m/Y \à\\s H:i') . ".";

        $words = preg_replace('/\s\s+/', "\n\n", $string);

        // Notifish: mensagem + PDF (URL pública do storage)
        $this->notifish->sendMessage(env('NOTIFISH_UUID'), $words, $this->whatsapp, $filename);
        $responsePdf = $this->notifish->sendArchive(env('NOTIFISH_UUID'), $publicUrl, $this->whatsapp, $filename, 'pdf');

        $isSuccess = (isset($responsePdf['success']) && $responsePdf['success'] === true)
            || (isset($responsePdf['status']) && strtolower((string) $responsePdf['status']) === 'success')
            || (isset($responsePdf['message']) && strtolower((string) $responsePdf['message']) === 'success');

        if ($isSuccess) {
            $database['envio_whatsapp'] = 'YES';
            $database['envio_whatsapp_em'] = Carbon::now();
        }else {
            $database['envio_whatsapp'] = 'NO';
            $database['envio_whatsapp_em'] = null;
        }

        $this->saveDb($database);

        $this->info('PDF report processed successfully.');
    }

    public function saveDb($data){
      return Contabilidade::create($data);
    }
}