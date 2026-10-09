<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * CP-05: the app must not trust client-supplied X-Forwarded-For by default.
 */
class Cp05TrustedProxiesTest extends TestCase
{
    public function test_trusted_proxies_config_exists_and_defaults_to_empty(): void
    {
        $this->assertTrue(
            array_key_exists('trusted_proxies', config('app')),
            'config(app).trusted_proxies is not defined'
        );
        $this->assertSame('', (string) config('app.trusted_proxies'));
    }

    public function test_bootstrap_does_not_trust_wildcard_proxy(): void
    {
        $src = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertNotFalse($src);
        $this->assertStringNotContainsString(
            "trustProxies(at: '*')",
            $src,
            'bootstrap/app.php still trusts all proxies'
        );
    }

    public function test_request_ip_ignores_forwarded_header_when_no_proxies_trusted(): void
    {
        Request::setTrustedProxies([], -1);

        $r = Request::create('/x', 'GET');
        $r->server->set('REMOTE_ADDR', '203.0.113.5');
        $r->headers->set('X-Forwarded-For', '1.2.3.4');

        $this->assertSame('203.0.113.5', $r->ip());
    }

    public function test_request_ip_uses_forwarded_header_when_proxy_trusted(): void
    {
        Request::setTrustedProxies(['10.0.0.1'], -1);

        $r = Request::create('/x', 'GET');
        $r->server->set('REMOTE_ADDR', '10.0.0.1');
        $r->headers->set('X-Forwarded-For', '1.2.3.4');

        $this->assertSame('1.2.3.4', $r->ip());
    }
}