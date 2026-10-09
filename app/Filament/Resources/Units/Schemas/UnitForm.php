<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Unit Code')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Unit Name')
                    ->required()
                    ->maxLength(100),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
