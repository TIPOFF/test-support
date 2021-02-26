<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Models;

class TestSellableBooking extends TestSellable
{
    public int $participants = 4;
    public string $description = 'Test Sellable Booking';

    public function getParticipants(): int
    {
        return $this->participants;
    }
}
