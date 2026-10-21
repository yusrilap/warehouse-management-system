<?php

namespace App\Filament\Resources\StockOpnames;

use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use App\Filament\Resources\StockOpnames\Pages\EditStockOpname;
use App\Filament\Resources\StockOpnames\Pages\ListStockOpnames;
use App\Filament\Resources\StockOpnames\Schemas\StockOpnameForm;
use App\Filament\Resources\StockOpnames\Tables\StockOpnamesTable;
use App\Filament\Resources\StockOpnames\Pages\ViewStockOpname;
use App\Models\StockOpname;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockOpnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnamesTable::configure($table);
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
        return static::getAuthUser()?->can('view stock opnames') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::getAuthUser()?->can('create stock opnames') ?? false;
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('opname_number')
                    ->label('Opname Number'),

                TextEntry::make('opname_date')
                    ->label('Opname Date')
                    ->date('d M Y'),

                TextEntry::make('warehouse.name')
                    ->label('Warehouse'),

                TextEntry::make('user.name')
                    ->label('Created By')
                    ->placeholder('-'),

                TextEntry::make('notes')
                    ->label('Notes')
                    ->placeholder('-')
                    ->columnSpanFull(),

                RepeatableEntry::make('items')
                    ->label('Opname Items')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Product'),

                        TextEntry::make('location.code')
                            ->label('Location'),

                        TextEntry::make('system_quantity')
                            ->label('System Quantity')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('physical_quantity')
                            ->label('Physical Quantity')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('variance')
                            ->label('Variance')
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
            'index' => ListStockOpnames::route('/'),
            'create' => CreateStockOpname::route('/create'),
            'view' => ViewStockOpname::route('/{record}'),
        ];
    }
}
