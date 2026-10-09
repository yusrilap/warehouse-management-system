<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('code')
                    ->label('Location Code')
                    ->required()
                    ->maxLength(50),

                TextInput::make('name')
                    ->label('Location Name')
                    ->maxLength(100),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
