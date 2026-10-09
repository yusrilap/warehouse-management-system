<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(50)
                    ->unique('ignoreRecord: true'),

                TextInput::make('barcode')
                    ->label('Barcode')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Product Name')
                    ->required()
                    ->maxLength(150),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->serachable()
                    ->preload()
                    ->required(),

                Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name')
                    ->serachable()
                    ->preload()
                    ->required(),

                TextInput::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                
            ]);
    }
}
