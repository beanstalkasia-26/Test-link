<?php

namespace App\Enums;

final class Permission
{
    public const ContentEdit = 'content.edit';

    public const ContentSubmit = 'content.submit';

    public const ContentPublish = 'content.publish';

    public const ContentDelete = 'content.delete';

    public const MediaManage = 'media.manage';

    public const NavigationManage = 'navigation.manage';

    public const SettingsManage = 'settings.manage';

    public const UsersManage = 'users.manage';

    public const RedirectsManage = 'redirects.manage';

    public const EnquiriesView = 'enquiries.view';

    public const EnquiriesExport = 'enquiries.export';

    public const AuditView = 'audit.view';

    public static function all(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }
}
