<?php

namespace Tests\Feature\Notifish;

use App\Repositories\NotifishRepository;
use App\Services\LnService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotifishRepositoryTest extends TestCase
{
    public function test_sendArchive_converts_integer_identifier_to_string_and_normalizes_phone(): void
    {
        $_ENV['NOTIFISH_BASE_URL'] = 'https://notifish.test/api/v2/';
        $_ENV['NOTIFISH_API_KEY'] = 'test-key';

        Http::fake(function ($request) {
            $payload = $request->data();

            $this->assertSame('55888877776666', $payload['recipient_phone']);
            $this->assertSame('123', $payload['identifier']); // int -> string
            $this->assertSame('pdf', $payload['media'][0]['type']); // forced in test call

            return Http::response(['success' => true, 'status' => 'success'], 200);
        });

        $repo = new NotifishRepository(new LnService());
        $resp = $repo->sendArchive('uuid', 'https://example.test/file', '55888877776666', 123, 'pdf');

        $this->assertTrue($resp['success']);
    }

    public function test_sendMessage_normalizes_phone_with_leading_55(): void
    {
        $_ENV['NOTIFISH_BASE_URL'] = 'https://notifish.test/api/v2/';
        $_ENV['NOTIFISH_API_KEY'] = 'test-key';

        Http::fake(function ($request) {
            $payload = $request->data();
            $this->assertSame('5511999999999', $payload['recipient_phone']); // not 5555...
            $this->assertSame('ola', $payload['message']);
            return Http::response(['success' => true, 'status' => 'success'], 200);
        });

        $repo = new NotifishRepository(new LnService());
        $resp = $repo->sendMessage('uuid', 'ola', '5511999999999', 'abc');

        $this->assertTrue($resp['success']);
    }
}

