<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json(['user' => $user]);
    }
}