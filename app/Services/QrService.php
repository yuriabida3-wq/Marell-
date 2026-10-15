<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * L9: local QR generation. No third-party call; the payload never leaves the server.
 *
 * Output is an SVG data URL suitable for <img src="...">. Sizeable payloads
 * (URLs, tokens) render in a fraction of the network cost of an external call.
 */
class QrService
{
    /**
     * Returns a data URL: data:image/svg+xml;base64,<...>
     */
    public static function svgDataUrl(string $payload, int $size = 250, string $ecLevel = 'M'): string
    {
        $level = match (strtoupper($ecLevel)) {
            'L'     => ErrorCorrectionLevel::L(),
            'Q'     => ErrorCorrectionLevel::Q(),
            'H'     => ErrorCorrectionLevel::H(),
            default => ErrorCorrectionLevel::M(),
        };

        $renderer = new ImageRenderer(
            new RendererStyle(max(80, $size), 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $svg = $writer->writeString(
            $payload,
            Encoder::DEFAULT_BYTE_MODE_ENCODING,
            $level
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
