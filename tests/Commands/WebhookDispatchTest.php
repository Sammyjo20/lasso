<?php

namespace Sammyjo20\Lasso\Tests\Commands;

use Mockery as m;
use Sammyjo20\Lasso\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Tasks\Publish\PublishJob;
use Illuminate\Support\Facades\{Http,Storage};

class WebhookDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('assets');

        $this->webhooks = $this->webhooks();

        config(['lasso.storage.environment' => 'staging']);
        
        Http::fake(['example.com/*' => Http::response('', 200)]);
    }

    /** @test */
    function it_can_dispatch_webhooks_per_environment_after_successful_publish_command(): void
    {
        $mock = m::mock($artisan = new Artisan);
        $mock->shouldReceive('note')->twice()->andReturn($artisan);
        $this->app->instance('Sammyjo20\Lasso\Container\Artisan', $mock);
        $publishJob = new PublishJob;

        $publishJob->dispatchWebhooks($this->webhooks);

        Http::assertSentCount(2);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://example.com/staging';
        });
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://example.com/always';
        });
        Http::assertNotSent(function (Request $request) {
            return $request->url() === 'http://example.com/production';
        });
    }

    private function webhooks(): array
    {
        return [
            'always'     => ['https://example.com/always'],
            'staging'    => ['https://example.com/staging'],
            'production' => ['https://example.com/production'],
        ];
    }

    public function tearDown(): void
    {
        m::close();
    }
}