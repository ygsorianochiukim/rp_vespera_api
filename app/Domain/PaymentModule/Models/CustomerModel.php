<?php

namespace App\Domain\PaymentModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CustomerModel extends Model
{
    use HasFactory;
    protected $table = 'wbs_i_SMSStatus';

    protected $primaryKey = 'customer_locks_id';

    protected $fillable = [
        'name1',
        'phone',
        'module',
        'failed_attempts',
        'locked_until',
    ];

    // Casts
    protected $casts = [
        'locked_until' => 'datetime',
    ];

    /**
     * Check if the customer is currently locked
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked_until && Carbon::now()->lessThan($this->locked_until);
    }

    /**
     * Increment failed attempts and lock if needed
     *
     * @param int $maxAttempts
     * @param int $lockMinutes
     */
    public function incrementAttempts(int $maxAttempts = 5, int $lockMinutes = 15): void
    {
        $this->failed_attempts++;

        if ($this->failed_attempts >= $maxAttempts) {
            $this->locked_until = Carbon::now()->addMinutes($lockMinutes);
        }

        $this->save();
    }

    /**
     * Reset attempts and unlock customer
     */
    public function resetAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }
}