<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Domain\Models\Permission;
use Modules\Auth\Domain\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create default roles
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Full access to all features', 'is_system' => true]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Administrative access', 'is_system' => true]
        );

        $editor = Role::firstOrCreate(
            ['slug' => 'editor'],
            ['name' => 'Editor', 'description' => 'Content management access', 'is_system' => false]
        );

        $user = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'description' => 'Basic user access', 'is_system' => true]
        );

        // Create default permissions
        $permissions = [
            // Users
            ['slug' => 'users.view', 'name' => 'View Users', 'module' => 'users', 'group' => 'users'],
            ['slug' => 'users.create', 'name' => 'Create Users', 'module' => 'users', 'group' => 'users'],
            ['slug' => 'users.edit', 'name' => 'Edit Users', 'module' => 'users', 'group' => 'users'],
            ['slug' => 'users.delete', 'name' => 'Delete Users', 'module' => 'users', 'group' => 'users'],

            // Roles
            ['slug' => 'roles.view', 'name' => 'View Roles', 'module' => 'auth', 'group' => 'roles'],
            ['slug' => 'roles.create', 'name' => 'Create Roles', 'module' => 'auth', 'group' => 'roles'],
            ['slug' => 'roles.edit', 'name' => 'Edit Roles', 'module' => 'auth', 'group' => 'roles'],
            ['slug' => 'roles.delete', 'name' => 'Delete Roles', 'module' => 'auth', 'group' => 'roles'],

            // Content
            ['slug' => 'articles.view', 'name' => 'View Articles', 'module' => 'content', 'group' => 'articles'],
            ['slug' => 'articles.create', 'name' => 'Create Articles', 'module' => 'content', 'group' => 'articles'],
            ['slug' => 'articles.edit', 'name' => 'Edit Articles', 'module' => 'content', 'group' => 'articles'],
            ['slug' => 'articles.delete', 'name' => 'Delete Articles', 'module' => 'content', 'group' => 'articles'],
            ['slug' => 'articles.publish', 'name' => 'Publish Articles', 'module' => 'content', 'group' => 'articles'],

            // Pages
            ['slug' => 'pages.view', 'name' => 'View Pages', 'module' => 'content', 'group' => 'pages'],
            ['slug' => 'pages.create', 'name' => 'Create Pages', 'module' => 'content', 'group' => 'pages'],
            ['slug' => 'pages.edit', 'name' => 'Edit Pages', 'module' => 'content', 'group' => 'pages'],
            ['slug' => 'pages.delete', 'name' => 'Delete Pages', 'module' => 'content', 'group' => 'pages'],

            // Media
            ['slug' => 'media.view', 'name' => 'View Media', 'module' => 'media', 'group' => 'media'],
            ['slug' => 'media.upload', 'name' => 'Upload Media', 'module' => 'media', 'group' => 'media'],
            ['slug' => 'media.delete', 'name' => 'Delete Media', 'module' => 'media', 'group' => 'media'],

            // Settings
            ['slug' => 'settings.view', 'name' => 'View Settings', 'module' => 'settings', 'group' => 'settings'],
            ['slug' => 'settings.edit', 'name' => 'Edit Settings', 'module' => 'settings', 'group' => 'settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign all permissions to admin role
        $allPermissions = Permission::pluck('slug')->toArray();
        $admin->syncPermissions($allPermissions);

        // Assign content permissions to editor
        $editorPermissions = Permission::whereIn('module', ['content', 'media'])
            ->whereIn('group', ['articles', 'pages', 'media'])
            ->pluck('slug')
            ->toArray();
        $editor->syncPermissions($editorPermissions);
    }
}
