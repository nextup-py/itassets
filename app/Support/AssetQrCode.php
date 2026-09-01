<?php

namespace App\Support;

use App\Models\Asset;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class AssetQrCode
{
    public static function url(Asset $asset): string
    {
        return route('assets.public', $asset);
    }

    public static function dataUri(Asset $asset, int $scale = 6): string
    {
        $qrCode = new QRCode(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => $scale,
        ]));

        return $qrCode->render(static::url($asset));
    }
}
