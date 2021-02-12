<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Models\TestModelStub;

/**
 * In order to support actAs(..) in tests, the Stub user model needs
 * to extend the authenticatble user and implement a few permission
 * related methods.
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, UserInterface
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use MustVerifyEmail;
    use TestModelStub;
    use Notifiable;

    public function hasRole($roles, string $guard = null): bool
    {
        return true;
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return true;
    }
}
