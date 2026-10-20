<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Services\StockOpnameService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = Auth::id();

        try {
            return app(StockOpnameService::class)->create($data);
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Stock Opname Failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();

            throw $exception;
        }
    }
}