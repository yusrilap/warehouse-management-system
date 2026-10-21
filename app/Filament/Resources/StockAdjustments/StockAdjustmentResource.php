<?php

namespace App\Filament\Resources\StockAdjustments;

use App\Filament\Resources\StockAdjustments\Pages\CreateStockAdjustment;
use App\Filament\Resources\StockAdjustments\Pages\EditStockAdjustment;
use App\Filament\Resources\StockAdjustments\Pages\ListStockAdjustments;
use App\Filament\Resources\StockAdjustments\Schemas\StockAdjustmentForm;
use App\Filament\Resources\StockAdjustments\Tables\StockAdjustmentsTable;
use App\Filament\Resources\StockAdjustments\Pages\ViewStockAdjustment;
use App\Models\StockAdjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockAdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockAdjustmentsTable::configure($table);
    }

    protected static function getAuthUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function canViewAny(): bool
    {
        return static::getAuthUser()?->can('view stock adjustments') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::getAuthUser()?->can('create stock adjustments') ?? false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('adjustment_number')
                    ->label('Adjustment Number'),

                TextEntry::make('adjustment_date')
                    ->label('Adjustment Date')
                    ->date('d M Y'),

                TextEntry::make('reason')
                    ->label('Reason'),

                TextEntry::make('user.name')
                    ->label('Created By')
                    ->placeholder('-'),

                TextEntry::make('notes')
                    ->label('Notes')
                    ->placeholder('-'),

                RepeatableEntry::make('items')
                    ->label('Items')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Product'),

                        TextEntry::make('location.warehouse.code')
                            ->label('Warehouse'),

                        TextEntry::make('location.code')
                            ->label('Location'),

                        TextEntry::make('quantity_before')
                            ->label('Before')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('quantity_after')
                            ->label('After')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('difference')
                            ->label('Difference')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('product.unit.code')
                            ->label('Unit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAdjustments::route('/'),
            'create' => CreateStockAdjustment::route('/create'),
            'view' => ViewStockAdjustment::route('/{record}'),
        ];
    }
}
