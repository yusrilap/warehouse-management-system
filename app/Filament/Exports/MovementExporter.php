<?php

namespace App\Filament\Exports;

use App\Models\StockMovement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use App\Models\GoodsReceipt;
use App\Models\StockAdjustment;
use App\Models\StockOpname;
use App\Models\StockOut;
use App\Models\StockTransfer;

class MovementExporter extends Exporter
{
    protected static ?string $model = StockMovement::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Date & Time'),

            ExportColumn::make('product.sku')
                ->label('SKU'),

            ExportColumn::make('product.name')
                ->label('Product'),

            ExportColumn::make('location.warehouse.name')
                ->label('Warehouse'),

            ExportColumn::make('location.code')
                ->label('Location'),

            ExportColumn::make('type')
                ->label('Movement Type')
                ->formatStateUsing(
                    fn ($state): string => match ($state) {
                        StockMovement::TYPE_GOODS_RECEIPT => 'Goods Receipt',
                        StockMovement::TYPE_STOCK_OUT => 'Stock Out',
                        StockMovement::TYPE_TRANSFER_IN => 'Transfer In',
                        StockMovement::TYPE_TRANSFER_OUT => 'Transfer Out',
                        StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                        default => $state,
                    }
                ),

            ExportColumn::make('reference_number')
                ->label('Reference')
                ->state(function (StockMovement $record): string {
                    $reference = $record->reference;

                    if (! $reference) {
                        return '-';
                    }

                    return match (true) {
                        $reference instanceof GoodsReceipt =>
                            $reference->receipt_number,

                        $reference instanceof StockOut =>
                            $reference->stock_out_number,

                        $reference instanceof StockTransfer =>
                            $reference->transfer_number,

                        $reference instanceof StockAdjustment =>
                            $reference->adjustment_number,

                        $reference instanceof StockOpname =>
                            $reference->opname_number,

                        default => '#' . $record->reference_id,
                    };
                }),

            ExportColumn::make('quantity')
                ->label('Movement'),

            ExportColumn::make('quantity_before')
                ->label('Before'),

            ExportColumn::make('quantity_after')
                ->label('After'),

            ExportColumn::make('product.unit.code')
                ->label('Unit'),

            ExportColumn::make('user.name')
                ->label('User'),

            ExportColumn::make('notes')
                ->label('Notes'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your movement export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
