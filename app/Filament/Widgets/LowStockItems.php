<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Stock;
use Filament\Tables\Columns\TextColumn;

class LowStockItems extends TableWidget
{

    protected static ?string $heading = 'Low Stock Items';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->with([
                        'product.unit',
                        'location.warehouse',
                    ])
                    ->whereHas('product', function ($query) {
                        $query->whereColumn(
                            'stocks.quantity',
                            '<=',
                            'products.minimum_stock'
                        );
                    })
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('location.warehouse.name')
                    ->label('Warehouse'),

                TextColumn::make('location.code')
                    ->label('Location'),

                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('product.minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('shortage')
                    ->label('Shortage')
                    ->state(fn (Stock $record): float =>
                        max(
                            0,
                            (float) $record->product->minimum_stock
                            - (float) $record->quantity
                        )
                    )
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),
            ])
            ->paginated([5]);
    }
}
