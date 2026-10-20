<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use App\Models\Location;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('opname_number')
                    ->label('Opname Number')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                DatePicker::make('opname_date')
                    ->label('Opname Date')
                    ->required()
                    ->default(now()),

                Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->relationship(
                        name: 'warehouse',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('is_active', true)
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Items')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(
                                Product::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required(),

                        Select::make('location_id')
                            ->label('Location')
                            ->options(function (Get $get) {
                                $warehouseId = $get('../../warehouse_id');

                                if (! $warehouseId) {
                                    return [];
                                }

                                return Location::query()
                                    ->where('warehouse_id', $warehouseId)
                                    ->where('is_active', true)
                                    ->orderBy('code')
                                    ->pluck('code', 'id');
                            })
                            ->searchable()
                            ->required(),

                        TextInput::make('physical_quantity')
                            ->label('Physical Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel('Add Item')
                    ->columnSpanFull(),
            ]);
    }
}