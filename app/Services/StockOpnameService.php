<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOpnameService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(array $data): StockOpname
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException(
                'Stock opname must have at least one item.'
            );
        }

        return DB::transaction(function () use ($data) {
            $stockOpname = StockOpname::create([
                'opname_number' => $data['opname_number'],
                'opname_date' => $data['opname_date'],
                'warehouse_id' => $data['warehouse_id'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $physicalQuantity = (float) $itemData['physical_quantity'];

                if ($physicalQuantity < 0) {
                    throw new InvalidArgumentException(
                        'Physical quantity cannot be negative.'
                    );
                }

                $product = Product::findOrFail(
                    $itemData['product_id']
                );

                $location = Location::findOrFail(
                    $itemData['location_id']
                );

                if ($location->warehouse_id !== (int) $data['warehouse_id']) {
                    throw new InvalidArgumentException(
                        'Selected location does not belong to the selected warehouse.'
                    );
                }

                $stock = Stock::query()
                    ->where('product_id', $product->id)
                    ->where('location_id', $location->id)
                    ->lockForUpdate()
                    ->first();

                $systemQuantity = $stock
                    ? (float) $stock->quantity
                    : 0;

                $variance = $physicalQuantity - $systemQuantity;

                $stockOpname->items()->create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'variance' => $variance,
                ]);

                if ($variance != 0) {
                    $this->inventoryService->adjustStock(
                        product: $product,
                        location: $location,
                        actualQuantity: $physicalQuantity,
                        reference: $stockOpname,
                        userId: $data['user_id'] ?? null,
                        notes: 'Stock Opname ' . $stockOpname->opname_number,
                    );
                }
            }

            return $stockOpname->load([
                'warehouse',
                'items.product',
                'items.location',
            ]);
        });
    }
}