<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\SubUser;

Route::get('/', function () {
    return view('welcome');
});

//Test
Route::middleware('paseto')->get('/profile', function (Request $request) {
    $user = SubUser::select('id', 'fullname', 'email')->find($request->attributes->get('user_id'));

    return response()->json([
        'message' => 'This is a protected route',
        'user' => $user,
    ]);
});


