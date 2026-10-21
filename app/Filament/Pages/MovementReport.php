<?php

namespace App\Filament\Pages;

use App\Models\StockMovement;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Exports\MovementExporter;
use Filament\Actions\ExportAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class MovementReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.movement-report';

    protected static ?string $title = 'Movement Report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Movement Report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->with([
                        'product.unit',
                        'location.warehouse',
                        'user',
                        'reference',
                    ])
            )
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
                    ->sortable(),

                TextColumn::make('location.code')
                    ->label('Location')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Movement Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        StockMovement::TYPE_GOODS_RECEIPT => 'Goods Receipt',
                        StockMovement::TYPE_STOCK_OUT => 'Stock Out',
                        StockMovement::TYPE_TRANSFER_IN => 'Transfer In',
                        StockMovement::TYPE_TRANSFER_OUT => 'Transfer Out',
                        StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): array => match ($state) {
                        StockMovement::TYPE_GOODS_RECEIPT,
                        StockMovement::TYPE_TRANSFER_IN => Color::Green,

                        StockMovement::TYPE_STOCK_OUT,
                        StockMovement::TYPE_TRANSFER_OUT => Color::Red,

                        StockMovement::TYPE_ADJUSTMENT => Color::Amber,

                        default => Color::Gray,
                    }),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->state(function (StockMovement $record): string {
                        $reference = $record->reference;

                        if (! $reference) {
                            return '-';
                        }

                        return match (true) {
                            $reference instanceof \App\Models\GoodsReceipt =>
                                $reference->receipt_number,

                            $reference instanceof \App\Models\StockOut =>
                                $reference->stock_out_number,

                            $reference instanceof \App\Models\StockTransfer =>
                                $reference->transfer_number,

                            $reference instanceof \App\Models\StockAdjustment =>
                                $reference->adjustment_number,

                            $reference instanceof \App\Models\StockOpname =>
                                $reference->opname_number,

                            default => '#' . $record->reference_id,
                        };
                    }),

                TextColumn::make('quantity')
                    ->label('Movement')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->summarize([
                        Summarizer::make()
                            ->label('Total In')
                            ->using(
                                fn ($query) => (clone $query)
                                    ->where('quantity', '>', 0)
                                    ->sum('quantity')
                            )
                            ->numeric(decimalPlaces: 2),

                        Summarizer::make()
                            ->label('Total Out')
                            ->using(
                                fn ($query) => abs(
                                    (float) (clone $query)
                                        ->where('quantity', '<', 0)
                                        ->sum('quantity')
                                )
                            )
                            ->numeric(decimalPlaces: 2),
                    ]),

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
                    ->placeholder('-')
                    ->limit(40),
            ])

            ->filters([
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

                SelectFilter::make('type')
                    ->label('Movement Type')
                    ->options([
                        StockMovement::TYPE_GOODS_RECEIPT => 'Goods Receipt',
                        StockMovement::TYPE_STOCK_OUT => 'Stock Out',
                        StockMovement::TYPE_TRANSFER_IN => 'Transfer In',
                        StockMovement::TYPE_TRANSFER_OUT => 'Transfer Out',
                        StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                    ]),
            ])

            ->toolbarActions([
                ExportAction::make()
                    ->exporter(MovementExporter::class)
                    ->visible(function (): bool {
                        $user = Auth::user();

                        return $user instanceof User
                            && $user->can('export movement report');
                    })
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && $user->can('view movement report');
    }
}