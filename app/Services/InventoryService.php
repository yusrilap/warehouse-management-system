<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function increaseStock(
        Product $product,
        Location $location,
        float $quantity,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
    ): Stock {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Stock increase quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $location,
            $quantity,
            $type,
            $reference,
            $userId,
            $notes
        ) {
            $stock = Stock::query()
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = Stock::create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => 0,
                ]);
            }

            $quantityBefore = (float) $stock->quantity;
            $quantityAfter = $quantityBefore + $quantity;

            $stock->update([
                'quantity' => $quantityAfter,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            return $stock;
        });
    }

    public function decreaseStock(
        Product $product,
        Location $location,
        float $quantity,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
    ): Stock {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Stock decrease quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $location,
            $quantity,
            $type,
            $reference,
            $userId,
            $notes
        ) {
            $stock = Stock::query()
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new InvalidArgumentException(
                    'Stock is not available at the selected location.'
                );
            }

            $quantityBefore = (float) $stock->quantity;

            if ($quantityBefore < $quantity) {
                throw new InvalidArgumentException(
                    'Insufficient stock. Available: ' . $quantityBefore
                );
            }

            $quantityAfter = $quantityBefore - $quantity;

            $stock->update([
                'quantity' => $quantityAfter,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'type' => $type,

                // Movement keluar disimpan negatif.
                'quantity' => -$quantity,

                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            return $stock;
        });
    }
}