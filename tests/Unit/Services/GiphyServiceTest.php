<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\GiphyService;
use App\Contracts\Services\GiphyServiceInterface;
use Illuminate\Support\Facades\Http;
use Mockery;
use Log;

class GiphyServiceTest extends TestCase
{
    protected GiphyService $giphyService;
    protected $adapterMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear mock del adaptador
        $this->adapterMock = Mockery::mock(GiphyServiceInterface::class);
        
        // Instanciar GiphyService con el mock
        $this->giphyService = new GiphyService($this->adapterMock);
    }

    public function test_search_returns_formatted_response()
    {
        Log::info('Test: Configurando mock para devolver array vacío');
        $this->adapterMock->shouldReceive('search')
            ->with('funny cats', 1, 0)
            ->andReturn([
                'total' => 100,
                'count' => 1,
                'offset' => 0,
                'data' => [
                    [
                        'id' => 'abc123',
                        'title' => 'Funny Cat',
                        'url' => 'https://giphy.com/abc123',
                        'images' => [
                            'original' => 'https://media.giphy.com/media/abc123/giphy.gif',
                            'downsized' => 'https://media.giphy.com/media/abc123/giphy-downsized.gif'
                        ],
                        'rating' => 'g',
                        'user' => null
                    ]
                ]
            ]);

        $result = $this->giphyService->search('funny cats', 1, 0);
         Log::info('Test: Resultado del servicio:', $result);
        $this->assertIsArray($result);
        $this->assertEquals(100, $result['total']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('abc123', $result['data'][0]['id']);
    }

    public function test_search_returns_empty_on_failure()
    {
        $this->adapterMock->shouldReceive('search')
            ->with('funny cats', 25, 0)
            ->andReturn([]);

        $result = $this->giphyService->search('funny cats');
  Log::info('Test: Resultado del servicio (vacío):', $result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_search_relanza_giphy_api_exception_cuando_adaptador_falla()
{
    $this->adapterMock->shouldReceive('search')
        ->with('funny cats', 25, 0)
        ->andThrow(new \App\Exceptions\GiphyApiException('Error simulado de GIPHY', 502));

    $this->expectException(\App\Exceptions\GiphyApiException::class);
    $this->expectExceptionCode(502);
    $this->expectExceptionMessage('Error simulado de GIPHY');
    
    $this->giphyService->search('funny cats');
}
}