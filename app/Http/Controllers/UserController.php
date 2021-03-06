<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->only(['update']);
    }
    public function register(RegisterRequest $request)
    {
        return User::create([
            'password' => Hash::make($request->password)
        ] +$request->only(['name', 'login']));
    }
    public function update(Request $request){
        $user = Auth::user();
        if (isset($user)) {
            $user->update($request->all());
            return response($user, 202);
        }
            return response()->json([
                'message' => "No permission",
            ], 422);

    }

    public function login(LoginRequest $request)
    {
        if ($user = User::where('login', $request->login)->first()) {
            if($user && Hash::check($request->password, $user->password)) {
                $user->generateToken();

                return [
                    'token' => $user->api_token,
                    'username' => $user->name,
                    'IsAdmin' => $user->IsAdmin,
                    'IsMaster' => $user->IsMaster,
                ];
            } else {
                return response()->json([
                    'message' => "The given data was invalid.",
                    'errors' => [
                        'login' => ['Не верный логин или пароль']
                    ]
                ], 422);
            }
            User::create([
                    'password' => Hash::make($request->password),
                    'name' => $request->login
                ] +$request->only([ 'login']));
            return $this->login($request);

        } else {
            User::create([
                    'password' => Hash::make($request->password),
                    'name' => $request->login
                ] +$request->only([ 'login']));
            return $this->login($request);
        }


    }
}
