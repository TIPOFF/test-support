<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Tests;

class BaseTestCaseTest extends TestCase
{
    /** @test */
    public function permissioned_user()
    {
        $user = self::createPermissionedUser('permission', true);
        $this->assertTrue($user->hasPermissionTo('permission'));
        $this->assertFalse($user->hasPermissionTo('anything-else'));

        $user = self::createPermissionedUser('permission', false);
        $this->assertFalse($user->hasPermissionTo('permission'));
        $this->assertFalse($user->hasPermissionTo('anything-else'));
    }
}
