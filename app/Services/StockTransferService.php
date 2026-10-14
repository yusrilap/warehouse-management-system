<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockTransferService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(array $data): StockTransfer
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException(
                'Stock transfer must have at least one item.'
            );
        }

        return DB::transaction(function () use ($data) {
            $stockTransfer = StockTransfer::create([
                'transfer_number' => $data['transfer_number'],
                'transfer_date' => $data['transfer_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $quantity = (float) $itemData['quantity'];

                if ($quantity <= 0) {
                    throw new InvalidArgumentException(
                        'Transfer quantity must be greater than zero.'
                    );
                }

                $product = Product::findOrFail(
                    $itemData['product_id']
                );

                $fromLocation = Location::findOrFail(
                    $itemData['from_location_id']
                );

                $toLocation = Location::findOrFail(
                    $itemData['to_location_id']
                );

                if ($fromLocation->id === $toLocation->id) {
                    throw new InvalidArgumentException(
                        'Source and destination locations must be different.'
                    );
                }

                $stockTransfer->items()->create([
                    'product_id' => $product->id,
                    'from_location_id' => $fromLocation->id,
                    'to_location_id' => $toLocation->id,
                    'quantity' => $quantity,
                ]);

                $this->inventoryService->transferStock(
                    product: $product,
                    fromLocation: $fromLocation,
                    toLocation: $toLocation,
                    quantity: $quantity,
                    reference: $stockTransfer,
                    userId: $data['user_id'] ?? null,
                    notes: 'Stock Transfer ' . $stockTransfer->transfer_number,
                );
            }

            return $stockTransfer->load([
                'items.product',
                'items.fromLocation',
                'items.toLocation',
            ]);
        });
    }
}