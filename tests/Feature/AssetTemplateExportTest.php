<?php

use App\Exports\AssetTemplateExport;
use App\Imports\AssetImport;
use App\Models\Asset;

it('has headings matching what the asset importer expects', function () {
    $importerColumns = [
        'asset_tag', 'nombre', 'categoria', 'marca', 'modelo',
        'numero_de_serie', 'estado', 'empleado_asignado', 'ubicacion',
        'fecha_de_compra', 'proveedor', 'costo',
    ];

    $headings = (new AssetTemplateExport)->headings();

    foreach ($headings as $heading) {
        expect($importerColumns)->toContain($heading);
    }
});

it('array rows have the same keys as the headings', function () {
    $export = new AssetTemplateExport;

    foreach ($export->array() as $row) {
        expect(array_keys($row))->toBe($export->headings());
    }
});

it('the template rows are actually importable by AssetImport', function () {
    $import = new AssetImport;

    foreach ((new AssetTemplateExport)->array() as $row) {
        $asset = $import->model($row);

        expect($asset)->toBeInstanceOf(Asset::class);
        expect(Asset::where('asset_tag', $row['asset_tag'])->exists())->toBeTrue();
    }
});
