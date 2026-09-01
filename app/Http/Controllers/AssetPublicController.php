<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Support\AssetQrCode;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AssetPublicController extends Controller
{
    public function show(Asset $asset): View
    {
        return view('assets.public', [
            'asset' => $asset->loadMissing('category'),
        ]);
    }

    public function qrImage(Asset $asset): Response
    {
        $dataUri = AssetQrCode::dataUri($asset, scale: 12);
        $png = base64_decode(str($dataUri)->after('base64,')->toString());

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr_' . $asset->asset_tag . '.png"',
        ]);
    }
}
