<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Supplier Code')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Supplier Name')
                    ->required()
                    ->maxLength(150),

                TextInput::make('contact_person')
                    ->label('Contact Person')
                    ->maxLength(100),

                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->maxLength(30),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(150),

                Textarea::make('address')
                    ->label('Address')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
