<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceiptService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(array $data): GoodsReceipt
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException(
                'Goods receipt must have at least one item.'
            );
        }

        return DB::transaction(function () use ($data) {
            $goodsReceipt = GoodsReceipt::create([
                'receipt_number' => $data['receipt_number'],
                'supplier_id' => $data['supplier_id'],
                'receipt_date' => $data['receipt_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $quantity = (float) $itemData['quantity'];

                if ($quantity <= 0) {
                    throw new InvalidArgumentException(
                        'Goods receipt quantity must be greater than zero.'
                    );
                }

                $product = Product::findOrFail(
                    $itemData['product_id']
                );

                $location = Location::findOrFail(
                    $itemData['location_id']
                );

                $goodsReceipt->items()->create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => $quantity,
                ]);

                $this->inventoryService->increaseStock(
                    product: $product,
                    location: $location,
                    quantity: $quantity,
                    type: StockMovement::TYPE_GOODS_RECEIPT,
                    reference: $goodsReceipt,
                    userId: $data['user_id'] ?? null,
                    notes: 'Goods Receipt ' . $goodsReceipt->receipt_number,
                );
            }

            return $goodsReceipt->load([
                'supplier',
                'items.product',
                'items.location',
            ]);
        });
    }
}