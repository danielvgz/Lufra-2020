<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     */
    public function index()
    {
        // Puedes devolver 'welcome' o crear una vista 'home'
        return view('Frontend.Home'); 
    }
}