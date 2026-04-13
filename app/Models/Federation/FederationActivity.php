<?php

namespace App\Models\Federation;

use Illuminate\Database\Eloquent\Model;

class FederationActivity extends Model
{
    protected $fillable = [
        'direction',
        'type',
        'payload',
        'source_domain',
        'target_domain',
        'status',
        'attempts',
        'error_message',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}
