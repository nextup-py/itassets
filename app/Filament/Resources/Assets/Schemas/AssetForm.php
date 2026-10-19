<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Filament\Resources\AssetCategories\Schemas\AssetCategoryForm;
use App\Filament\Resources\Locations\Schemas\LocationForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Support\Currencies;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AssetForm
{
    private static function isMobileCategory(Get $get): bool
    {
        $categoryId = $get('asset_category_id');

        return $categoryId && (bool) AssetCategory::find($categoryId)?->is_mobile;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // ── Información general ──────────────────────────────────────
                Section::make('Información general')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('asset_tag')
                            ->label('Código')
                            ->placeholder('Se genera automáticamente')
                            ->maxLength(20)
                            ->unique(Asset::class, 'asset_tag', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Nombre / Descripción')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('asset_category_id')
                            ->label('Categoría')
                            ->required()
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm(fn (Schema $schema) => AssetCategoryForm::configure($schema))
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options(Asset::STATUSES)
                            ->default('stock')
                            ->columnSpan(1),

                        Select::make('condition')
                            ->label('Condición')
                            ->options(Asset::CONDITIONS)
                            ->columnSpan(1),

                        TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('serial_number')
                            ->label('Número de serie')
                            ->maxLength(100)
                            ->columnSpan(1),

                        FileUpload::make('photo')
                            ->label('Fotografía')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('assets/photos')
                            ->maxSize(3072)
                            ->imagePreviewHeight('160')
                            ->helperText('JPG, PNG o WEBP, máx. 3MB.')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                // ── Dispositivo móvil ────────────────────────────────────────
                Section::make('Dispositivo móvil')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->description('Solo para categorías marcadas como "Dispositivo móvil".')
                    ->visible(fn (Get $get): bool => self::isMobileCategory($get))
                    ->schema([
                        TextInput::make('imei')
                            ->label('IMEI')
                            ->maxLength(20)
                            ->unique(Asset::class, 'imei', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('imei2')
                            ->label('IMEI 2 (dual SIM)')
                            ->maxLength(20)
                            ->unique(Asset::class, 'imei2', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('phone_number')
                            ->label('Número de línea')
                            ->maxLength(30)
                            ->columnSpan(1),

                        TextInput::make('carrier')
                            ->label('Operador')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Select::make('plan_type')
                            ->label('Tipo de plan')
                            ->options(Asset::PLAN_TYPES)
                            ->columnSpan(1),

                        TextInput::make('monthly_plan_cost')
                            ->label('Costo mensual del plan')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                // ── Adquisición ───────────────────────────────────────────────
                Section::make('Adquisición')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Fecha de compra')
                            ->displayFormat(current_date_format())
                            ->columnSpan(1),

                        TextInput::make('purchase_price')
                            ->label('Precio de compra')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('Se interpreta en la moneda seleccionada abajo.')
                            ->columnSpan(1),

                        Select::make('currency')
                            ->label('Moneda')
                            ->helperText('En blanco = moneda base de la instalación.')
                            ->options(Currencies::LIST)
                            ->searchable()
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                // ── Proveedor y ubicación ────────────────────────────────────────
                Section::make('Proveedor y ubicación')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Proveedor de compra')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierForm::configure($schema))
                            ->columnSpan(1),

                        Select::make('location_id')
                            ->label('Ubicación')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => LocationForm::configure($schema))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                // ── Garantía ──────────────────────────────────────────────────
                Section::make('Garantía')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        DatePicker::make('warranty_expiry_date')
                            ->label('Fecha de vencimiento de garantía')
                            ->displayFormat(current_date_format())
                            ->columnSpan(1),

                        Select::make('warranty_supplier_id')
                            ->label('Proveedor de garantía')
                            ->relationship('warrantySupplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierForm::configure($schema))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

            ]);
    }
}
