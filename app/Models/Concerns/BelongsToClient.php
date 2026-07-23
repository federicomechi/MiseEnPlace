<?php

namespace App\Models\Concerns;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClient
{
    protected static function bootBelongsToClient(): void
    {
        static::addGlobalScope('client', function (Builder $builder): void {
            $clientId = auth()->user()?->client_id;
            if ($clientId !== null) {
                $builder->where($builder->getModel()->getTable().'.client_id', $clientId);
            }
        });

        static::creating(function ($model): void {
            if ($model->client_id === null && auth()->user()?->client_id !== null) {
                $model->client_id = auth()->user()->client_id;
            }
        });
    }

    /** @return BelongsTo<Client, $this> */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
