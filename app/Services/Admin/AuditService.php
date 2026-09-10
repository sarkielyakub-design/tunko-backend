<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(

        $admin,

        string $module,

        string $action,

        string $description,

        array $old = [],

        array $new = []

    ): void {

        AuditLog::create([

            "admin_id" => $admin->id,

            "module" => $module,

            "action" => $action,

            "description" => $description,

            "ip_address" => request()->ip(),

            "user_agent" => request()->userAgent(),

            "old_values" => $old,

            "new_values" => $new,

        ]);

    }
}