<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;
use Filament\Tables\Columns\TextColumn;


class RecentStockMovements extends TableWidget
{

    protected static ?string $heading = 'Recent Stock Movements';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->with([
                        'product.unit',
                        'location.warehouse',
                    ])
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            StockMovement::TYPE_GOODS_RECEIPT => 'Goods Receipt',
                            StockMovement::TYPE_STOCK_OUT => 'Stock Out',
                            StockMovement::TYPE_TRANSFER_IN => 'Transfer In',
                            StockMovement::TYPE_TRANSFER_OUT => 'Transfer Out',
                            StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                            default => $state ?? '-',
                        }
                    ),

                TextColumn::make('quantity')
                    ->label('Movement')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),

                TextColumn::make('location.code')
                    ->label('Location'),

                TextColumn::make('location.warehouse.name')
                    ->label('Warehouse'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5]);
    }
}
