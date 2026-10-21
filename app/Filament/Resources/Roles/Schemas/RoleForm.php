<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;


class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Role Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->relationship(
                        name: 'permissions',
                        titleAttribute: 'name'
                    )
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable(),
            ]);
    }
}
