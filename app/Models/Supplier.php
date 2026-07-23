<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToClient;

    protected $fillable = ['client_id', 'filemaker_id', 'name', 'contact_name', 'email', 'phone', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<SupplierPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(SupplierPrice::class);
    }
}
