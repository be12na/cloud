<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard member.
     * Dilindungi oleh middleware auth + role:member di route.
     */
    public function index(Request $request): View
    {
        return view('member.dashboard', [
            'user' => $request->user(),
        ]);
    }
}
