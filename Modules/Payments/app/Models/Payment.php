<?php

namespace Modules\Payments\Models;

use App\Enums\PaymentStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Models\Order;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'status' => PaymentStatusEnum::class,
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isAlreadyVerified(): bool
    {
        return $this->status === PaymentStatusEnum::SUCCESS;
    }

    public function isPendingToVerify(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    public function markAsVerified(?Carbon $paid_at = null): bool
    {
        return $this->update([
            'status' => PaymentStatusEnum::SUCCESS,
            'paid_at' => $paid_at ?? now(),
        ]);
    }

    public function scopeForOrder(Builder $query, int $order_id): Builder
    {
        return $query->whereOrderId($order_id);
    }
}
