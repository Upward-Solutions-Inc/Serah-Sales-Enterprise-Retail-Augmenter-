<?php

namespace App\Models\Core\Auth;

use Altek\Eventually\Eventually;
use App\Models\Core\Auth\Traits\Attribute\UserAttribute;
use App\Models\Core\Auth\Traits\Boot\UserBootTrait;
use App\Models\Core\Auth\Traits\Method\HasRoles;
use App\Models\Core\Auth\Traits\Method\UserMethod;
use App\Models\Core\Auth\Traits\Method\UserStatus;
use App\Models\Core\Auth\Traits\Relationship\UserRelationship;
use App\Models\Core\Auth\Traits\Rules\UserRules;
use App\Models\Core\Auth\Traits\Scope\UserScope;
use App\Models\Pos\Inventory\BranchOrWarehouse;
use App\Models\Tenant\Branch\Relationship\TenantUserRelationShip;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;


class User extends BaseUser implements HasLocalePreference
{
    protected static $logAttributes = [
        'first_name', 'last_name', 'email', 'branch_or_warehouse_id'
    ];

    use UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope,
        HasRoles,
        UserRules,
        UserBootTrait,
        LogsActivity,
        Eventually,
        Notifiable,
        CausesActivity,
        TenantUserRelationShip,
        UserStatus;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function preferredLocale()
    {
        return app()->getLocale() ?? 'en';
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $class_alias_array = explode('\\', get_called_class());
        $class_name = strtolower(end($class_alias_array));

        return trans('default.log_description_message', [
            'model' => trans('default.' . $class_name),
            'event' => trans('default.' . $eventName)
        ]);
    }

    public function branchOrWarehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BranchOrWarehouse::class, 'branch_or_warehouse_id');
    }
}
