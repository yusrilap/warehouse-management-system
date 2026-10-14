<?php

namespace App\Filament\Resources\StockOuts\Pages;

use App\Filament\Resources\StockOuts\StockOutResource;
use App\Services\StockOutService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CreateStockOut extends CreateRecord
{
    protected static string $resource = StockOutResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = Auth::id();

        try {
            return app(StockOutService::class)->create($data);
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Stock Out Failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();

            throw $exception;
        }
    }
}