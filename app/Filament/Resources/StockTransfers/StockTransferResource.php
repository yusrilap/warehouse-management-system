<?php

namespace App\Filament\Resources\StockTransfers;

use App\Filament\Resources\StockTransfers\Pages\CreateStockTransfer;
use App\Filament\Resources\StockTransfers\Pages\EditStockTransfer;
use App\Filament\Resources\StockTransfers\Pages\ListStockTransfers;
use App\Filament\Resources\StockTransfers\Schemas\StockTransferForm;
use App\Filament\Resources\StockTransfers\Tables\StockTransfersTable;
use App\Filament\Resources\StockTransfers\Pages\ViewStockTransfer;
use App\Models\StockTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockTransfersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected static function getAuthUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function canViewAny(): bool
    {
        return static::getAuthUser()?->can('view stock transfers') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::getAuthUser()?->can('create stock transfers') ?? false;
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

    public static function getPages(): array
    {
        return [
            'index' => ListStockTransfers::route('/'),
            'create' => CreateStockTransfer::route('/create'),
            'view' => ViewStockTransfer::route('/{record}'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('transfer_number')
                    ->label('Transfer Number'),

                TextEntry::make('transfer_date')
                    ->label('Transfer Date')
                    ->date('d M Y'),

                TextEntry::make('reference_number')
                    ->label('Reference Number')
                    ->placeholder('-'),

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

                        TextEntry::make('fromLocation.warehouse.code')
                            ->label('From Warehouse'),

                        TextEntry::make('fromLocation.code')
                            ->label('From Location'),

                        TextEntry::make('toLocation.warehouse.code')
                            ->label('To Warehouse'),

                        TextEntry::make('toLocation.code')
                            ->label('To Location'),

                        TextEntry::make('quantity')
                            ->label('Quantity')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('product.unit.code')
                            ->label('Unit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
