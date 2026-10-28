<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Redirects signed-in users to their personal notes dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Redirect to the signed-in user's notes.
     *
     * @return RedirectResponse A redirect to the user's note list.
     */
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('notes.index');
    }
}
