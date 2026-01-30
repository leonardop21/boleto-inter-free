<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contabilidade extends Model
{
    use HasFactory;

    protected $table = 'contabilidades';
    protected $fillable = [
        'nome',
        'email',
        'whatsapp',
        'arquivo_pdf',
        'envio_email',
        'envio_whatsapp',
        'envio_email_em',
        'envio_whatsapp_em'
    ];
}
