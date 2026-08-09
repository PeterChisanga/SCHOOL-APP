<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * The school a request should be scoped to: the school a platform_admin
     * is currently "viewing as" (session), or the logged-in user's own school.
     */
    protected function currentSchoolId()
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->user_type === 'platform_admin') {
            return session('acting_school_id');
        }

        return $user->school_id;
    }
}
