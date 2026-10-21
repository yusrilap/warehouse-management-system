<?php

namespace App\Filament\Pages;

use App\Models\Stock;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LowStock extends Page implements HasTable
{
    use InteractsWithTable;
 
    protected string $view = 'filament.pages.low-stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $title = 'Low Stock';

    protected static ?string $navigationLabel = 'Low Stock';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->whereHas('product', function (Builder $query) {
                        $query->whereColumn(
                            'stocks.quantity',
                            '<=',
                            'products.minimum_stock'
                        );
                    })
            )
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
                    ->searchable(),

                TextColumn::make('location.code')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('product.minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('shortage')
                    ->label('Shortage')
                    ->state(function (Stock $record): float {
                        return max(
                            0,
                            (float) $record->product->minimum_stock
                                - (float) $record->quantity
                        );
                    })
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('product.unit.code')
                    ->label('Unit'),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (): string => 'Low Stock')
                    ->badge()
                    ->color(Color::Red),

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

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship(
                        'product',
                        'name'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('quantity');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && $user->can('view low stock');
    }
}