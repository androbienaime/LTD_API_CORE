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
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AccountController extends Controller
{
    public function register(RegisterAccountRequest $request)
    {
        $validated = $request->validated();

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

        if($request->has("address")){
            $account->address()->attach($request->address);
        }

        if ($request->hasFile('account_cover')) {
            // Supprimer l'ancienne image de couverture si elle existe
            if ($account->getFirstMedia('account_cover')) {
                $account->getFirstMedia('account_cover')->delete();
            }
            $account->addMedia($request->file('account_cover'))
                ->toMediaCollection('account_cover');
        }

        if ($request->hasFile('account_profile')) {
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
    }

    public function login(Request $request){

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {
            if (!$token = auth("account-service")->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                    'data' => null,
                ], 401);
            }
        
            return response()->json([
                'status' => 'success',
                'message' => 'Authentication successful',
                'data' => [
                    'token' => $token,
                    'type' => 'bearer'
                ],
            ]);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 400);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired',
            ], 401);
        } catch (\Exception $e) {
            Log::error("Authentication error", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
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
