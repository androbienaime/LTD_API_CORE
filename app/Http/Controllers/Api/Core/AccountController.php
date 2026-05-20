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

   public function refresh(Request $request)
{
    try {
        // Accepte le token depuis :
        // 1. Le body : { "refresh_token": "eyJ..." }
        // 2. Le header Authorization: Bearer eyJ...
        $oldToken = $request->input('refresh_token')
            ?? JWTAuth::getToken();
 
        if (!$oldToken) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token manquant',
            ], 400);
        }
 
        // Générer le nouveau access token (courte durée)
        $newAccessToken = JWTAuth::setToken($oldToken)->refresh();
 
        // ✅ Rotation du refresh token :
        // On génère un nouveau token avec une TTL longue (30 jours)
        // à partir du payload du nouvel access token
        $payload      = JWTAuth::setToken($newAccessToken)->getPayload();
        $subject      = $payload->get('sub');
        $newRefreshToken = JWTAuth::fromUser(
            auth('account-service')->getProvider()->retrieveById($subject)
        );
 
        // Surcharger la TTL du refresh token (30 jours)
        $newRefreshToken = JWTAuth::customClaims(['exp' => now()->addDays(30)->timestamp])
            ->fromUser(
                auth('account-service')->getProvider()->retrieveById($subject)
            );
 
        return response()->json([
            'status'        => 'success',
            'message'       => 'Token rafraîchi avec succès',
            'data'          => [
                'token'         => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type'    => 'bearer',
                'expires_in'    => config('jwt.ttl') * 60,
            ],
        ]);
 
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Token expiré, veuillez vous reconnecter',
        ], 401);
 
    } catch (TokenInvalidException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Token invalide',
        ], 401);
 
    } catch (\Exception $e) {
        Log::error('Refresh token error', ['error' => $e->getMessage()]);
        return response()->json([
            'status'  => 'error',
            'message' => 'Impossible de rafraîchir le token',
        ], 500);
    }
}

}
