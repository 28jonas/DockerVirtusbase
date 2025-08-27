<?php
namespace App\Enums;

class FamilyRolePermission
{
    public const PERMISSIONS = [
        'owner' => [
            'manage_family', 'invite_members', 'remove_members', 'assign_roles',
            'create_global_content', 'edit_global_content', 'delete_global_content', 'view_all_content',
        ],
        'parent' => [
            'create_global_content', 'edit_global_content', 'view_all_content',
        ],
        'child' => [
            'view_own_content', 'edit_own_content',
        ],
        'guest' => [
            'view_own_content',
        ],
    ];

    public static function getPermissionsForRole(string $role): array
    {
        return self::PERMISSIONS[$role] ?? [];
    }

    public static function can(string $role, string $permission): bool
    {
        return in_array($permission, self::getPermissionsForRole($role));
    }
}
