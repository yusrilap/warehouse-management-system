<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\Location;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('adjustment_number')
                    ->label('Adjustment Number')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                DatePicker::make('adjustment_date')
                    ->label('Adjustment Date')
                    ->required()
                    ->default(now()),

                Select::make('reason')
                    ->label('Reason')
                    ->options([
                        'Physical stock correction' => 'Physical Stock Correction',
                        'Damaged material' => 'Damaged Material',
                        'Data correction' => 'Data Correction',
                        'Found excess stock' => 'Found Excess Stock',
                    ])
                    ->searchable()
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
                            ->options(
                                Location::query()
                                    ->where('is_active', true)
                                    ->orderBy('code')
                                    ->pluck('code', 'id')
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('actual_quantity')
                            ->label('Actual Quantity')
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