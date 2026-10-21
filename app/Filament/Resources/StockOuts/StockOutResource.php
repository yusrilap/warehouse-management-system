<?php

namespace App\Filament\Resources\StockOuts;

use App\Filament\Resources\StockOuts\Pages\CreateStockOut;
use App\Filament\Resources\StockOuts\Pages\EditStockOut;
use App\Filament\Resources\StockOuts\Pages\ListStockOuts;
use App\Filament\Resources\StockOuts\Schemas\StockOutForm;
use App\Filament\Resources\StockOuts\Tables\StockOutsTable;
use App\Filament\Resources\StockOuts\Pages\ViewStockOut;
use App\Models\StockOut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class StockOutResource extends Resource
{
    protected static ?string $model = StockOut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockOutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOutsTable::configure($table);
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
        return static::getAuthUser()?->can('view stock outs') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::getAuthUser()?->can('create stock outs') ?? false;
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
            'index' => ListStockOuts::route('/'),
            'create' => CreateStockOut::route('/create'),
            'view' => ViewStockOut::route('/{record}'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('stock_out_number')
                    ->label('Stock Out Number'),

                TextEntry::make('stock_out_date')
                    ->label('Stock Out Date')
                    ->date('d M Y'),

                TextEntry::make('destination')
                    ->label('Destination'),

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

                        TextEntry::make('location.warehouse.code')
                            ->label('Warehouse'),

                        TextEntry::make('location.code')
                            ->label('Location'),

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
