<?php

namespace Tests\Unit\Adapters;

use Tests\TestCase;
use App\Infrastructure\Adapters\GiphyApiAdapter;
use Illuminate\Support\Facades\Http;
use App\Exceptions\GiphyApiException;

class GiphyApiAdapterTest extends TestCase
{
    protected GiphyApiAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new GiphyApiAdapter();
    }

    public function test_search_lanza_exception_cuando_giphy_500()
    {
        Http::fake([
            'api.giphy.com/v1/gifs/search*' => Http::response(null, 500)
        ]);

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionCode(500);
        
        $this->adapter->search('funny cats');
    }

    public function test_search_lanza_exception_504_en_timeout()
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Timeout');
        });

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionCode(504);
        
        $this->adapter->search('funny cats');
    }

    public function test_search_devuelve_vacio_en_404()
    {
        Http::fake([
            'api.giphy.com/v1/gifs/search*' => Http::response(null, 404)
        ]);

        $result = $this->adapter->search('funny cats');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_find_by_id_devuelve_null_en_404()
    {
        Http::fake([
            'api.giphy.com/v1/gifs/abc123' => Http::response(null, 404)
        ]);

        $result = $this->adapter->findById('abc123');
        
        $this->assertNull($result);
    }
}