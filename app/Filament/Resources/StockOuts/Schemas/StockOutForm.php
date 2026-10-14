<?php

namespace App\Filament\Resources\StockOuts\Schemas;

use App\Models\Location;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockOutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stock_out_number')
                    ->label('Stock Out Number')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                DatePicker::make('stock_out_date')
                    ->label('Stock Out Date')
                    ->required()
                    ->default(now()),

                TextInput::make('destination')
                    ->label('Destination')
                    ->required()
                    ->maxLength(150),

                TextInput::make('reference_number')
                    ->label('Reference Number')
                    ->maxLength(100),

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

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),
                    ])
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel('Add Item')
                    ->columnSpanFull(),
            ]);
    }
}