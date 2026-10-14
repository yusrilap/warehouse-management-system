<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use App\Services\StockAdjustmentService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = Auth::id();

        try {
            return app(StockAdjustmentService::class)->create($data);
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Stock Adjustment Failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();

            throw $exception;
        }
    }
}