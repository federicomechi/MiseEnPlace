<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['filemaker_id', 'name', 'location_id', 'location_name', 'email', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'date'];
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
