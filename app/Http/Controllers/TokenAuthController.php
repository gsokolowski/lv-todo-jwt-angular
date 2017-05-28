<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use Illuminate\Support\Facades\Hash;

class TokenAuthController extends Controller
{

    // display all users
    public function index(){

        $users  = User::all();
        return response()->json($users);

    }

    // create new authenticated user in db
    // POST http://localhost:8000/api/register with json payload
    // x-www-form-encoded {'name' : 'Marta, 'email': 'marta@gmail.com', 'password': 'abc1234'}
    // returns 200 on success
    public function register(Request $request){

        $newUser= $request->all();
        $password=Hash::make($request->input('password'));
        $newUser['password'] = $password;

        if (User::create($newUser)) {
            return response()->json(['user registerd'], 201);
        } else {
            return response()->json(['user not registered'], 500);
        }
    }

    // authenticate and return token if user and password is valid
    // POST http://localhost:8000/api/authenticate
    // with json payload
    // x-www-form-encoded {'email': 'marta@gmail.com', 'password': 'abc1234'}
    // returns token valid only fo 1h
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    // get user info
    public function getAuthenticatedUser()
    {
        try {
            // if not authenticated user give 404
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // user is authenticated return all user info
        return response()->json(compact('user'));
    }

}
