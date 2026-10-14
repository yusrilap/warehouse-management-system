<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{

    public const TYPE_GOODS_RECEIPT = 'GOODS_RECEIPT';

    public const TYPE_STOCK_OUT = 'STOCK_OUT';

    public const TYPE_TRANSFER_IN = 'TRANSFER_IN';

    public const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    protected $fillable = [
        'product_id',
        'location_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'quantity_before'   => 'decimal:2',
            'quantity_after'    => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
