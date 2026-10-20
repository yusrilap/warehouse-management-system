<?php

namespace App\Filament\Pages;

use App\Models\Stock;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Exports\StockExporter;
use Filament\Actions\ExportAction;

class StockReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.stock-report';

    protected static ?string $title = 'Stock Report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Stock Report';

    public function table(Table $table): Table
    {
        return $table
            ->query(Stock::query())
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
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Quantity')
                            ->numeric(decimalPlaces: 2)
                    ),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),

                TextColumn::make('product.minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('stock_status')
                    ->label('Status')
                    ->state(function (Stock $record): string {
                        return (float) $record->quantity
                            <= (float) $record->product->minimum_stock
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

                Filter::make('stock_status')
                    ->label('Stock Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'normal' => 'Normal',
                                'low_stock' => 'Low Stock',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['status'] ?? null;

                        if ($status === 'low_stock') {
                            return $query->whereHas(
                                'product',
                                fn (Builder $productQuery) =>
                                    $productQuery->whereColumn(
                                        'stocks.quantity',
                                        '<=',
                                        'products.minimum_stock'
                                    )
                            );
                        }

                        if ($status === 'normal') {
                            return $query->whereHas(
                                'product',
                                fn (Builder $productQuery) =>
                                    $productQuery->whereColumn(
                                        'stocks.quantity',
                                        '>',
                                        'products.minimum_stock'
                                    )
                            );
                        }

                        return $query;
                    }),
            ])

            ->toolbarActions([
                ExportAction::make()
                    ->label('Export Stock Report')
                    ->exporter(StockExporter::class),
            ])
            ->defaultSort('product.name');
    }
}