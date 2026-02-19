<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index(): Response
    {
        return Inertia::render('home', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
