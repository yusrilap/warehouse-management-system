<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

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
                    ->searchable(),

                TextColumn::make('location.code')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Movement Type')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'GOODS_RECEIPT' => 'Goods Receipt',
                            'STOCK_OUT' => 'Stock Out',
                            'TRANSFER_IN' => 'Transfer In',
                            'TRANSFER_OUT' => 'Transfer Out',
                            'ADJUSTMENT' => 'Adjustment',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): array => match ($state) {
                            'GOODS_RECEIPT' => Color::Green,
                            'TRANSFER_IN' => Color::Green,
                            'STOCK_OUT' => Color::Red,
                            'TRANSFER_OUT' => Color::Red,
                            'ADJUSTMENT' => Color::Amber,
                            default => Color::Gray,
                        }
                    ),

                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->state(function ($record): string {
                        $reference = $record->reference;

                        if (! $reference) {
                            return '-';
                        }

                        return match (true) {
                            $reference instanceof \App\Models\GoodsReceipt
                                => $reference->receipt_number,

                            $reference instanceof \App\Models\StockOut
                                => $reference->stock_out_number,

                            $reference instanceof \App\Models\StockTransfer
                                => $reference->transfer_number,

                            $reference instanceof \App\Models\StockAdjustment
                                => $reference->adjustment_number,

                            $reference instanceof \App\Models\StockOpname
                                => $reference->opname_number,

                            default => '#' . $reference->getKey(),
                        };
                    }),

                TextColumn::make('quantity')
                    ->label('Movement')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('quantity_before')
                    ->label('Before')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('quantity_after')
                    ->label('After')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('-'),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('-'),
            ])

            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship(
                        'product',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

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

                SelectFilter::make('type')
                    ->label('Movement Type')
                    ->options([
                        'GOODS_RECEIPT' => 'Goods Receipt',
                        'STOCK_OUT' => 'Stock Out',
                        'TRANSFER_IN' => 'Transfer In',
                        'TRANSFER_OUT' => 'Transfer Out',
                        'ADJUSTMENT' => 'Adjustment',
                    ]),

                Filter::make('created_at')
                    ->label('Date Range')
                    ->schema([
                        DatePicker::make('from')
                            ->label('From'),

                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])

            ->defaultSort('created_at', 'desc');
    }
}