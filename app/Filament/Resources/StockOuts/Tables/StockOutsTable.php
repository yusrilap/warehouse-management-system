<?php

namespace App\Filament\Resources\StockOuts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockOutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stock_out_number')
                    ->label('Stock Out Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock_out_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('destination')
                    ->label('Destination')
                    ->searchable(),

                TextColumn::make('reference_number')
                    ->label('Reference Number')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                TextColumn::make('user.name')
                    ->label('Created By')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
