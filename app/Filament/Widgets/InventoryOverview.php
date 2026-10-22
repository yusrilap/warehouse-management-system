<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\GoodsReceipt;
use App\Models\StockOut;
use App\Models\StockTransfer;
use App\Models\StockAdjustment;
use App\Models\StockOpname;

class InventoryOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $todayTransactions =
            GoodsReceipt::whereDate('created_at', today())->count()
            + StockOut::whereDate('created_at', today())->count()
            + StockTransfer::whereDate('created_at', today())->count()
            + StockAdjustment::whereDate('created_at', today())->count()
            + StockOpname::whereDate('created_at', today())->count();

        return [
            Stat::make(
                'Active Products',
                Product::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('Products currently active')
                ->icon('heroicon-o-cube'),

            Stat::make(
                'Active Warehouses',
                Warehouse::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('Warehouses currently active')
                ->icon('heroicon-o-building-office-2'),

            Stat::make(
                'Low Stock',
                Stock::query()
                    ->whereHas('product', function ($query) {
                        $query->whereColumn('stocks.quantity', '<=', 'products.minimum_stock');
                    })
                    ->count()
            )
                ->description('Product locations below minimum')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make(
                "Today's Transactions",
                $todayTransactions
            )
                ->description('Inventory transactions created today')
                ->icon('heroicon-o-arrows-right-left'),
        ];
    }
}
