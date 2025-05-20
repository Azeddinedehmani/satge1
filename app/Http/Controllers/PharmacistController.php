<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PharmacistController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
  public function __construct()
{
    $this->middleware(\Illuminate\Auth\Middleware\Authenticate::class);
    $this->middleware(\App\Http\Middleware\PharmacistMiddleware::class);
}

    /**
     * Show the pharmacist dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Here you'd typically fetch dashboard data
        // For now we'll just return a basic view
        return view('pharmacist.dashboard');
    }
}