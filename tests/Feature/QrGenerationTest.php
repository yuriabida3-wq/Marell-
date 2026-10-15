<?php

namespace Tests\Feature;

use App\Services\QrService;
use Tests\TestCase;

/**
 * L9: QR generation is local. No quickchart.io calls anywhere.
 */
class QrGenerationTest extends TestCase
{
    public function test_qr_service_returns_svg_data_url(): void
    {
        $url = QrService::svgDataUrl('hello world', 250);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $url);
    }

    public function test_qr_service_base64_decodes_to_svg_markup(): void
    {
        $url = QrService::svgDataUrl('hello world', 250);
        $payload = base64_decode(substr($url, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<svg', $payload);
        $this->assertStringContainsString('width="250"', $payload);
        $this->assertStringContainsString('height="250"', $payload);
    }

    public function test_qr_service_accepts_custom_sizes(): void
    {
        $url = QrService::svgDataUrl('payload', 320);
        $payload = base64_decode(substr($url, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('width="320"', $payload);
    }

    public function test_qr_service_supports_all_error_levels(): void
    {
        foreach (['L', 'M', 'Q', 'H'] as $level) {
            $url = QrService::svgDataUrl('hello', 250, $level);
            $this->assertStringStartsWith('data:image/svg+xml;base64,', $url, "level $level failed");
        }
    }

    public function test_qr_hub_view_does_not_reference_quickchart(): void
    {
        $src = file_get_contents(base_path('resources/views/qr/hub.blade.php'));
        $this->assertStringNotContainsString('quickchart', $src);
    }

    public function test_qr_hub_view_uses_qrservice(): void
    {
        $src = file_get_contents(base_path('resources/views/qr/hub.blade.php'));
        $this->assertSame(3, substr_count($src, 'QrService::svgDataUrl'));
    }
}
