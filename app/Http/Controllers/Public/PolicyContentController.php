<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PolicyWindow;

class PolicyContentController extends Controller
{
    public function show(PolicyWindow $window){
        $policy = $window->policy()->select('title', 'description')->firstOrFail();

        return response()->json([
            'title' => $policy->title,
            'content' => $policy->description,
        ]);
    }
}
