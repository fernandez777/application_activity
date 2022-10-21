<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return "success";
    }

    public function edit(User $user, Request $request)
    {    
        $user->update($request->all());

        return $user;
    }

    public function destroy(User $user)
    {    
        $user->delete();

        return "deleted successfuly";
    }
}
