<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Models;

use Tipoff\Support\Contracts\Sellable\Sellable;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Models\TestModelStub;

class TestSellable extends BaseModel implements Sellable
{
    use TestModelStub;

    public string $description = 'Test Sellable';
    public ?string $itemId = null;
    public ?int $locationId = null;
    public ?string $taxCode = null;
    public ?array $metaData = null;

    public function getMorphClass(): string
    {
        return get_class($this);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getTaxCode(): ?string
    {
        return $this->taxCode;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function getItemId(): string
    {
        return (string) ($this->itemId ?? $this->id);
    }
}
