<?php

namespace App\Http\Controllers\Api\Core;

use App\Exceptions\ShopMembershipException;
use App\Http\Controllers\Controller;
use App\Models\Core\Account;
use App\Models\Core\Shop;
use App\Services\ShopMembershipService;
use Illuminate\Http\Request;

class ShopMemberController extends Controller
{
       public function __construct(
        private readonly ShopMembershipService $membershipService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $this->authorize('manageMembers', $shop);

        return response()->json(
            $shop->accounts()->with('pivot.role')->get()
        );
    }

    public function store(Request $request, Shop $shop)
    {
        $this->authorize('attachMember', $shop);

        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'role'       => 'required|string',
        ]);

        try {
            $membership = $this->membershipService->attach(
                $shop,
                Account::findOrFail($data['account_id']),
                $data['role']
            );

            return response()->json($membership, 201);

        } catch (ShopMembershipException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, Shop $shop, Account $account)
    {
        $this->authorize('updateMemberRole', $shop);

        $data = $request->validate(['role' => 'required|string']);

        try {
            $membership = $this->membershipService->updateRole($shop, $account, $data['role']);
            return response()->json($membership);
        } catch (ShopMembershipException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Shop $shop, Account $account)
    {
        $this->authorize('detachMember', $shop);

        try {
            $this->membershipService->detach($shop, $account);
            return response()->json(null, 204);
        } catch (ShopMembershipException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Shop $shop, Account $account)
    {
        //
    }
}
