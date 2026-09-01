<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Códigos QR</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 12px 8px;
            border: 1px dashed #ccc;
        }
        img { width: 110px; height: 110px; }
        .asset-tag { font-size: 12px; font-weight: bold; margin-top: 4px; }
        .asset-name { font-size: 10px; color: #555; }
    </style>
</head>
<body>

    <table>
        @foreach ($assets->chunk(3) as $row)
            <tr>
                @foreach ($row as $asset)
                    <td>
                        <img src="{{ \App\Support\AssetQrCode::dataUri($asset) }}">
                        <div class="asset-tag">{{ $asset->asset_tag }}</div>
                        <div class="asset-name">{{ \Illuminate\Support\Str::limit($asset->name, 30) }}</div>
                    </td>
                @endforeach
                @for ($i = $row->count(); $i < 3; $i++)
                    <td></td>
                @endfor
            </tr>
        @endforeach
    </table>

</body>
</html>
