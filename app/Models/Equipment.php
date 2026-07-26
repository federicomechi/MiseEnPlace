<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use BelongsToClient;

    protected $fillable = ['client_id', 'filemaker_id', 'name', 'code', 'category', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
