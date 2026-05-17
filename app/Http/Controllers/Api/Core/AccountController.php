<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterAccountRequest;
use App\Services\AccountService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AccountController extends Controller
{
    public function register(RegisterAccountRequest $request)
    {
        return AccountService::register($request->validated(), $request);
    }

    public function login(Request $request){

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        return AccountService::login($credentials);
    }

    // Controller
    public function refresh(Request $request)
    {
        try {
            // Lit depuis le body OU depuis le header Authorization
            $oldToken = $request->input('refresh_token') 
                ?? JWTAuth::getToken();

            if (! $oldToken) {
                return response()->json(['error' => 'Token manquant'], 400);
            }

            $newToken = JWTAuth::refresh($oldToken);

            return response()->json(['token' => $newToken]);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expiré'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Impossible de rafraîchir'], 500);
        }
    }


}
