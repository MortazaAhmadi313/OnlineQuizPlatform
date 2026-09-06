<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function hello()
    {
        return response()->json([
            'message' => 'Hello from the WelcomeController',
            'team'    => 'Team Alpha',
        ]);
    }

    public function greet($name)
    {
        return response()->json([
            'greeting' => "Hello, $name!",
            'from'     => 'WelcomeController'
        ]);
    }
}
