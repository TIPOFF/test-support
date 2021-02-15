<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Nova;

use Illuminate\Http\Request;
use Tipoff\Support\Nova\Resource;

class User extends Resource
{
    public static $model = \Tipoff\TestSupport\Models\User::class;

    public function fields(Request $request)
    {
    }
}
