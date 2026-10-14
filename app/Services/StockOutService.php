<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOutService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(array $data): StockOut
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException(
                'Stock out must have at least one item.'
            );
        }

        return DB::transaction(function () use ($data) {
            $stockOut = StockOut::create([
                'stock_out_number' => $data['stock_out_number'],
                'stock_out_date' => $data['stock_out_date'],
                'destination' => $data['destination'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $quantity = (float) $itemData['quantity'];

                if ($quantity <= 0) {
                    throw new InvalidArgumentException(
                        'Stock out quantity must be greater than zero.'
                    );
                }

                $product = Product::findOrFail(
                    $itemData['product_id']
                );

                $location = Location::findOrFail(
                    $itemData['location_id']
                );

                $stockOut->items()->create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $quantity,
                ]);

                $this->inventoryService->decreaseStock(
                    product: $product,
                    location: $location,
                    quantity: $quantity,
                    type: StockMovement::TYPE_STOCK_OUT,
                    reference: $stockOut,
                    userId: $data['user_id'] ?? null,
                    notes: 'Stock Out ' . $stockOut->stock_out_number,
                );
            }

            return $stockOut->load([
                'items.product',
                'items.location',
            ]);
        });
    }
}