<?php

namespace App\Filament\Exports;

use App\Models\Stock;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockExporter extends Exporter
{
    protected static ?string $model = Stock::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product.sku')
                ->label('SKU'),

            ExportColumn::make('product.name')
                ->label('Product'),

            ExportColumn::make('location.warehouse.name')
                ->label('Warehouse'),

            ExportColumn::make('location.code')
                ->label('Location'),

            ExportColumn::make('quantity')
                ->label('Current Stock'),

            ExportColumn::make('product.unit.code')
                ->label('Unit'),

            ExportColumn::make('product.minimum_stock')
                ->label('Minimum Stock'),

            ExportColumn::make('stock_status')
                ->label('Status')
                ->state(function (Stock $record): string {
                    return (float) $record->quantity
                        <= (float) $record->product->minimum_stock
                            ? 'Low Stock'
                            : 'Normal';
                }),

            ExportColumn::make('updated_at')
                ->label('Last Updated'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock report export has completed and '
            . number_format($export->successful_rows)
            . ' '
            . str('row')->plural($export->successful_rows)
            . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '
                . number_format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed to export.';
        }

        return $body;
    }
}