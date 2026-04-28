<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{
    public function create(): RedirectResponse
    {
        // Public registration disabled — accounts are created by admin/master
        return redirect()->route('login');
    }
}
