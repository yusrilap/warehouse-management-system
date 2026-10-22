<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\ViewAction;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use Filament\Schemas\Schema;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable(),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => ucfirst($state ?? '-')
                    ),

                TextColumn::make('subject_type')
                    ->label('Module')
                    ->formatStateUsing(
                        fn (?string $state): string => $state
                            ? class_basename($state)
                            : '-'
                    ),

                // Filter::make('created_at')
                //     ->label('Date Range')
                //     ->schema([
                //         DatePicker::make('from')
                //             ->label('From'),

                //         DatePicker::make('until')
                //             ->label('Until'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['from'] ?? null,
                //                 fn (Builder $query, $date): Builder =>
                //                     $query->whereDate('created_at', '>=', $date)
                //             )
                //             ->when(
                //                 $data['until'] ?? null,
                //                 fn (Builder $query, $date): Builder =>
                //                     $query->whereDate('created_at', '<=', $date)
                //             );
                //     }),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable(),

                TextColumn::make('subject_id')
                    ->label('Record ID')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                SelectFilter::make('subject_type')
                    ->label('Module')
                    ->options([
                        \App\Models\Category::class => 'Category',
                        \App\Models\Unit::class => 'Unit',
                        \App\Models\Product::class => 'Product',
                        \App\Models\Supplier::class => 'Supplier',
                        \App\Models\Warehouse::class => 'Warehouse',
                        \App\Models\Location::class => 'Location',

                        \App\Models\GoodsReceipt::class => 'Goods Receipt',
                        \App\Models\StockOut::class => 'Stock Out',
                        \App\Models\StockTransfer::class => 'Stock Transfer',
                        \App\Models\StockAdjustment::class => 'Stock Adjustment',
                        \App\Models\StockOpname::class => 'Stock Opname',
                    ]),

                SelectFilter::make('causer_id')
                    ->label('User')
                    ->options(
                        \App\Models\User::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query
                            ->where('causer_type', \App\Models\User::class)
                            ->where('causer_id', $data['value']);
                    }),
            ])

            ->recordActions([
                ViewAction::make()
                    ->label('View Changes')
                    ->modalHeading('Activity Log Details'),
            ])

            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }
}
