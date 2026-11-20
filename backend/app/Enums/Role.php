<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Publisher = 'publisher';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Publisher => 'Publisher',
            self::Editor => 'Editor',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => Permission::all(),
            self::Publisher => [
                Permission::ContentEdit, Permission::ContentSubmit, Permission::ContentPublish, Permission::ContentDelete,
                Permission::MediaManage, Permission::NavigationManage, Permission::RedirectsManage,
                Permission::EnquiriesView,
            ],
            self::Editor => [
                Permission::ContentEdit, Permission::ContentSubmit, Permission::MediaManage,
            ],
        };
    }
}
