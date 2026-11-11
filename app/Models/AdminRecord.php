<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRecord extends Model
{
    protected $fillable = ['module', 'title', 'data', 'created_by'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
