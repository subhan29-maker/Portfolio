<?php

namespace App\Models;

use App\Enums\BudgetRange;
use Illuminate\Database\Eloquent\Model;

/**
 * A message sent through the public contact form.
 */
class ContactMessage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'budget',
        'message',
        'ip_address',
        'user_agent',
        'notified_owner',
        'read_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget' => BudgetRange::class,
            'notified_owner' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }
}
