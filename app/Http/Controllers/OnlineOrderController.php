<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // TODO: Implement online order submission in Phase 4
        return response()->json(['status' => 'received'], 201);
    }
}
