<?php

namespace App\Services;

use App\Models\Core\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AccountService
{

    public static function register(array $validated, $request=null){
        try{
            $account = Account::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'] ?? null,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'type' => "account"
            ]);

            if($request && $request->has("address")){
                $account->address()->attach($request->address);
            }

            if ($request && $request->hasFile('account_cover')) {
                // Supprimer l'ancienne image de couverture si elle existe
                if ($account->getFirstMedia('account_cover')) {
                    $account->getFirstMedia('account_cover')->delete();
                }
                $account->addMedia($request->file('account_cover'))
                    ->toMediaCollection('account_cover');
            }

            if ($request && $request->hasFile('account_profile')) {
                // Supprimer l'ancienne image de couverture si elle existe
                if ($account->getFirstMedia('account_profile')) {
                    $account->getFirstMedia('account_profile')->delete();
                }
                $account->addMedia($request->file('account_profile'))
                    ->toMediaCollection('account_profile');
            }

            $token = JWTAuth::fromUser($account);

            return response()->json([
                'status' => 'success',
                'user' => $account,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Registration error", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not register account' . $e->getMessage(),
            ], 500);
        }
    }

public static function login(array $credentials){
    try {
        if (!$token = auth("account-service")->attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials',
                'data'    => null,
            ], 401);
        }
 
        $account = auth("account-service")->user();
 
        $account->load(['shop' => function ($query) {
            $query->wherePivot('deleted_at', null)
                  ->wherePivot('status', 'active')
                  ->withPivot(['created_at', 'status'])
                  ->orderBy('pivot_created_at', 'desc');
        }]);
 
        // ✅ Générer un refresh token avec une durée de vie plus longue
        // setTTL() est en minutes — ici 30 jours
        $refreshToken = auth("account-service")
            ->setTTL(60 * 24 * 30)
            ->tokenById($account->id);
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Authentication successful',
            'data'    => [
                'token'         => $token,        // accès court (config jwt.ttl)
                'refresh_token' => $refreshToken, // ✅ refresh long (30 jours)
                'type'          => 'bearer',
                'account'       => $account,
            ],
        ]);
 
    } catch (TokenInvalidException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid token',
        ], 400);
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Token has expired',
        ], 401);
    } catch (\Exception $e) {
        Log::error("Authentication error", ['error' => $e->getMessage()]);
        return response()->json([
            'status'  => 'error',
            'message' => 'Could not authenticate',
        ], 500);
    }
}

    public function profile()
    {
        return response()->json(auth('account-service')->user());
    }

    public function logout()
    {
        auth('account')->logout();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // public function refresh()
    // {
    //     $newToken = auth("account")->refresh();
        
    //     return response()->json([
    //         'message' => 'Token refreshed successfully',
    //         'token' => $newToken,
    //     ]);
    // }
}
