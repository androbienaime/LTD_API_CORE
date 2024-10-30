<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Core\Account;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    // /**
    //  * Determine whether the account can view any models.
    //  */
    // public function viewAny(Account|User $account): bool
    // {
    //     return $account->can('view_any_role');
    // }

    // /**
    //  * Determine whether the account can view the model.
    //  */
    // public function view(Account|User $account, Role $role): bool
    // {
    //     return $account->can('view_role');
    // }

    // /**
    //  * Determine whether the account can create models.
    //  */
    // public function create(Account|User $account): bool
    // {
    //     return $account->can('create_role');
    // }

    // /**
    //  * Determine whether the account can update the model.
    //  */
    // public function update(Account|User $account, Role $role): bool
    // {
    //     return $account->can('update_role');
    // }

    // /**
    //  * Determine whether the account can delete the model.
    //  */
    // public function delete(Account|User $account, Role $role): bool
    // {
    //     return $account->can('delete_role');
    // }

    // /**
    //  * Determine whether the account can bulk delete.
    //  */
    // public function deleteAny(Account|User $account): bool
    // {
    //     return $account->can('delete_any_role');
    // }

    // /**
    //  * Determine whether the account can permanently delete.
    //  */
    // public function forceDelete(Account|User $account, Role $role): bool
    // {
    //     return $account->can('{{ ForceDelete }}');
    // }

    // /**
    //  * Determine whether the account can permanently bulk delete.
    //  */
    // public function forceDeleteAny(Account|User $account): bool
    // {
    //     return $account->can('{{ ForceDeleteAny }}');
    // }

    // /**
    //  * Determine whether the account can restore.
    //  */
    // public function restore(Account|User $account, Role $role): bool
    // {
    //     return $account->can('{{ Restore }}');
    // }

    // /**
    //  * Determine whether the account can bulk restore.
    //  */
    // public function restoreAny(Account|User $account): bool
    // {
    //     return $account->can('{{ RestoreAny }}');
    // }

    // /**
    //  * Determine whether the account can replicate.
    //  */
    // public function replicate(Account|User $account, Role $role): bool
    // {
    //     return $account->can('{{ Replicate }}');
    // }

    // /**
    //  * Determine whether the account can reorder.
    //  */
    // public function reorder(Account|User $account): bool
    // {
    //     return $account->can('{{ Reorder }}');
    // }
}
