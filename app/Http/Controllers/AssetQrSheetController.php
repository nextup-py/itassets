<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AssetQrSheetController extends Controller
{
    public function show(string $token): Response
    {
        if (! (Auth::user()?->can('export_report') ?? false)) {
            throw new AuthorizationException;
        }

        $assetIds = Cache::get("qr_sheet.{$token}");
        abort_if($assetIds === null, 410, 'El enlace expiró. Volvé a intentarlo.');

        $assets = Asset::whereIn('id', $assetIds)->orderBy('asset_tag')->get();

        $pdf = Pdf::loadView('pdf.qr-sheet', ['assets' => $assets]);

        return $pdf->stream('codigos_qr.pdf');
    }
}
