<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Models;

use Tipoff\Support\Contracts\Sellable\Booking;

class TestSellableBooking extends TestSellable implements Booking
{
    public int $participants = 4;
    public string $description = 'Test Sellable Booking';

    public function getParticipants(): int
    {
        return $this->participants;
    }
}
