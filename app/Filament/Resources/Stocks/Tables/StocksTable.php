<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Colors\Color;
use Filament\Tables\Filters\Filter;


class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.code')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),

                TextColumn::make('product.minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->state(function ($record): string {
                        return (float) $record->quantity <= (float) $record->product->minimum_stock
                            ? 'Low Stock'
                            : 'Normal';
                    })
                    ->badge()
                    ->color(function (string $state): array {
                        return match ($state) {
                            'Low Stock' => Color::Red,
                            'Normal' => Color::Green,
                        };
                    }),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('warehouse')
                    ->label('Warehouse')
                    ->relationship(
                        'location.warehouse',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship(
                        'location',
                        'code'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship(
                        'product',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn ($query) =>
                        $query->whereHas('product', function ($productQuery) {
                            $productQuery->whereColumn(
                                'stocks.quantity',
                                '<=',
                                'products.minimum_stock'
                            );
                        })
                    ),
            ])

            ->defaultSort('updated_at', 'desc');

            
    }
}