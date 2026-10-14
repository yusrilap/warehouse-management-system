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

    public function transferStock(
        Product $product,
        Location $fromLocation,
        Location $toLocation,
        float $quantity,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
    ): void {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Transfer quantity must be greater than zero.'
            );
        }

        if ($fromLocation->id === $toLocation->id) {
            throw new InvalidArgumentException(
                'Source and destination locations must be different.'
            );
        }

        DB::transaction(function () use (
            $product,
            $fromLocation,
            $toLocation,
            $quantity,
            $reference,
            $userId,
            $notes
        ) {
            $this->decreaseStock(
                product: $product,
                location: $fromLocation,
                quantity: $quantity,
                type: StockMovement::TYPE_TRANSFER_OUT,
                reference: $reference,
                userId: $userId,
                notes: $notes,
            );

            $this->increaseStock(
                product: $product,
                location: $toLocation,
                quantity: $quantity,
                type: StockMovement::TYPE_TRANSFER_IN,
                reference: $reference,
                userId: $userId,
                notes: $notes,
            );
        });
    }

    public function adjustStock(
        Product $product,
        Location $location,
        float $actualQuantity,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
    ): Stock {
        if ($actualQuantity < 0) {
            throw new InvalidArgumentException(
                'Actual quantity cannot be negative.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $location,
            $actualQuantity,
            $reference,
            $userId,
            $notes
        ) {
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

            if (! $stock) {
                $stock = Stock::create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity' => 0,
                ]);
            }

            $stock->update([
                'quantity' => $actualQuantity,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'type' => StockMovement::TYPE_ADJUSTMENT,
                'quantity' => $difference,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $actualQuantity,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            return $stock;
        });
    }
}