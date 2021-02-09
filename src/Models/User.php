<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Support\Models\TestModelStub;

/**
 * In order to support actAs(..) in tests, the Stub user model needs
 * to extend the authenticatble user and implement a few permission
 * related methods.
 */
class User extends Authenticatable implements UserInterface
{
    use TestModelStub;

    protected $guarded = ['id'];

    public function hasRole($roles, string $guard = null): bool
    {
        return true;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return true;
    }
}
