<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Illuminate\Routing\Controller;
use LaracraftTech\LaravelSpyglass\Spyglass;

class HomeController extends Controller
{
    /**
     * Display the Spyglass view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('spyglass::layout', [
            'cssFile' => Spyglass::$useDarkTheme ? 'app-dark.css' : 'app.css',
            'spyglassScriptVariables' => Spyglass::scriptVariables(),
        ]);
    }
}
