<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthorizationService
{
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->role === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return in_array($user->role, $roles);
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Check if user can access resource
     */
    public function canAccess(string $resource, string $action = 'view'): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Superadmin has access to everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Define permissions based on role and resource
        $permissions = $this->getPermissions($user->role);
        
        return isset($permissions[$resource][$action]) && $permissions[$resource][$action];
    }

    /**
     * Get permissions for a role
     */
    private function getPermissions(string $role): array
    {
        $permissions = [
            'superadmin' => [
                'products' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'customers' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'sales' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'invoices' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true, 'approve' => true],
                'purchase_orders' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'purchase_entries' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'reports' => ['view' => true, 'export' => true],
                'users' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
            ],
            'admin' => [
                'products' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'customers' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'sales' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'invoices' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false, 'approve' => false],
                'purchase_orders' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'purchase_entries' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'reports' => ['view' => true, 'export' => true],
                'users' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
            ],
            'user' => [
                'products' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'customers' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'sales' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
                'invoices' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'approve' => false],
                'purchase_orders' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'purchase_entries' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'reports' => ['view' => false, 'export' => false],
                'users' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false],
            ],
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Check if user can edit specific resource
     */
    public function canEdit(string $resource, $model = null): bool
    {
        if (!$this->canAccess($resource, 'edit')) {
            return false;
        }

        // Additional checks for specific models
        if ($model && method_exists($model, 'canBeEditedBy')) {
            return $model->canBeEditedBy(Auth::user());
        }

        return true;
    }

    /**
     * Check if user can delete specific resource
     */
    public function canDelete(string $resource, $model = null): bool
    {
        if (!$this->canAccess($resource, 'delete')) {
            return false;
        }

        // Additional checks for specific models
        if ($model && method_exists($model, 'canBeDeletedBy')) {
            return $model->canBeDeletedBy(Auth::user());
        }

        return true;
    }

    /**
     * Get accessible resources for current user
     */
    public function getAccessibleResources(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $permissions = $this->getPermissions($user->role);
        $accessible = [];

        foreach ($permissions as $resource => $actions) {
            if (isset($actions['view']) && $actions['view']) {
                $accessible[] = $resource;
            }
        }

        return $accessible;
    }

    /**
     * Check if user can perform bulk operations
     */
    public function canPerformBulkOperations(string $resource): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('admin');
    }

    /**
     * Check if user can export data
     */
    public function canExport(string $resource): bool
    {
        return $this->canAccess($resource, 'export');
    }

    /**
     * Check if user can import data
     */
    public function canImport(string $resource): bool
    {
        return $this->canAccess($resource, 'create') && $this->isAdmin();
    }
}
