<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\Frontend\PixController;
use App\Interfaces\NotifishInterface;
use App\Interfaces\PixInterface;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FrontendPixControllerTest extends TestCase
{
    public function test_clienteAcessouFatura_sets_session_and_does_not_send_notification(): void
    {
        Session::start();

        $pix = \Mockery::mock(PixInterface::class);
        $notifish = \Mockery::mock(NotifishInterface::class);
        $notifish->shouldNotReceive('sendNotificationMessage'); // notificação "visualizou o pix" desativada

        $controller = new PixController($pix, $notifish);

        $record = (object) [
            'id' => 10,
            'customer' => (object) ['name' => 'Cliente X'],
        ];

        $this->assertTrue($controller->clienteAcessouFatura($record));
        $this->assertTrue(Session::has('pix' . $record->id));
        $this->assertFalse($controller->clienteAcessouFatura($record)); // segunda vez retorna false (já tem sessão)
    }
}

