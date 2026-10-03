<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Server-side QR generation (SVG, no external service, no secret leakage).
 * Replaces the previous third-party QR API which received TOTP URIs.
 */
class QrService
{
    public function svg(string $content, int $size = 200): string
    {
        $backend = new SvgImageBackEnd();
        $renderer = new ImageRenderer(new RendererStyle($size), $backend);

        return (new Writer($renderer))->writeString($content);
    }

    public function dataUri(string $content, int $size = 200): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($content, $size));
    }
}
