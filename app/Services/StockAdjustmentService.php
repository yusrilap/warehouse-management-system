<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(array $data): StockAdjustment
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException(
                'Stock adjustment must have at least one item.'
            );
        }

        return DB::transaction(function () use ($data) {
            $stockAdjustment = StockAdjustment::create([
                'adjustment_number' => $data['adjustment_number'],
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $actualQuantity = (float) $itemData['actual_quantity'];

                if ($actualQuantity < 0) {
                    throw new InvalidArgumentException(
                        'Actual quantity cannot be negative.'
                    );
                }

                $product = Product::findOrFail(
                    $itemData['product_id']
                );

                $location = Location::findOrFail(
                    $itemData['location_id']
                );

                $stock = Stock::query()
                    ->where('product_id', $product->id)
                    ->where('location_id', $location->id)
                    ->lockForUpdate()
                    ->first();

                $quantityBefore = $stock
                    ? (float) $stock->quantity
                    : 0;

                $difference = $actualQuantity - $quantityBefore;

                if ($difference == 0) {
                    throw new InvalidArgumentException(
                        'Actual quantity is the same as current stock.'
                    );
                }

                $stockAdjustment->items()->create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $actualQuantity,
                    'difference' => $difference,
                ]);

                $this->inventoryService->adjustStock(
                    product: $product,
                    location: $location,
                    actualQuantity: $actualQuantity,
                    reference: $stockAdjustment,
                    userId: $data['user_id'] ?? null,
                    notes: 'Stock Adjustment ' . $stockAdjustment->adjustment_number,
                );
            }

            return $stockAdjustment->load([
                'items.product',
                'items.location',
            ]);
        });
    }
}