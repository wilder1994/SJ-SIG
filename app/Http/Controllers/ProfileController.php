<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function __invoke(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }
}
