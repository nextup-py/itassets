<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\View\View;

class AssetPublicController extends Controller
{
    public function show(Asset $asset): View
    {
        return view('assets.public', [
            'asset' => $asset->loadMissing('category'),
        ]);
    }
}
