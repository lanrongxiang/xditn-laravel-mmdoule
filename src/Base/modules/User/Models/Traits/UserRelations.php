<?php

namespace Xditn\Base\modules\User\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Permissions\Enums\MenuType;
use Xditn\Base\modules\Permissions\Models\Jobs;
use Xditn\Base\modules\Permissions\Models\Permissions;
use Xditn\Base\modules\Permissions\Models\Roles;
use Xditn\MModule;
use Xditn\Support\Module\ModuleRepository;

trait UserRelations
{
    protected bool $isPermissionModuleEnabled = false;

    /**
     * init traits
     */
    public function initializeUserRelations(): void
    {
        $this->isPermissionModuleEnabled = app(ModuleRepository::class)->enabled('permissions');
        if ($this->isPermissionModuleEnabled) {
            $this->with = ['roles', 'jobs'];
        }
    }

    /**
     * roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany($this->getRolesModel(), 'user_has_roles', 'user_id', 'role_id');
    }

    /**
     * jobs
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany($this->getJobsModel(), 'user_has_jobs', 'user_id', 'job_id');
    }

    /**
     * permissions
     */
    public function withPermissions(): self
    {
        if (!$this->isPermissionModuleEnabled) {
            return $this;
        }
        /* @var Permissions $permissionsModel */
        $permissionsModel = app($this->getPermissionsModel());
        if ($this->isSuperAdmin()) {
            $permissions = $permissionsModel->orderByDesc('sort')->get();
        } else {
            $permissionIds = Collection::make();
            $this->roles()->with('permissions')->get()->each(function ($role) use (&$permissionIds)
                {
                    $permissionIds = $permissionIds->concat($role->permissions->pluck('id'));
                });
            $permissions = $permissionsModel->whereIn('id', $permissionIds->unique())->orderByDesc('sort')->get();
        }
        $this->setAttribute('permissions', $permissions->each(function ($permission)
        {
            $permission->setAttribute('hidden', $permission->isHidden());
            $permission->setAttribute('keepalive', $permission->isKeepAlive());
        }));
        return $this;
    }

    public function withMenu(): self
    {
        if (!$this->isPermissionModuleEnabled) {
            return $this;
        }
        /* @var Permissions $permissionsModel */
        $permissionsModel = app($this->getPermissionsModel());
        if ($this->isSuperAdmin()) {
            $permissions = $permissionsModel->setBeforeGetList(function ($query)
            {
                return $query->with('actions')->whereIn('type', [MenuType::Top->value(), MenuType::Menu->value()]
                )->orderByDesc('sort');
            })->getList();
        } else {
            $permissionIds = Collection::make();
            $this->roles()->with('permissions')->get()->each(function ($role) use (&$permissionIds)
                {
                    $permissionIds = $permissionIds->concat($role->permissions->pluck('id'));
                });
            $permissions = $permissionsModel->setBeforeGetList(function ($query) use ($permissionIds)
            {
                return $query->whereIn('id', $permissionIds->unique())->with('actions')->whereIn(
                    'type',
                    [MenuType::Top->value(), MenuType::Menu->value()]
                )->orderByDesc('sort');
            })->getList();
        }
        $this->setAttribute('permissions', $permissions->each(function ($permission)
        {
            //            $permission->setAttribute('hidden', $permission->isHidden());
            $permission->setAttribute('keepalive', $permission->isKeepAlive());
        }));
        return $this;
    }

    /**
     * permission module controller.action
     */
    public function can(?string $permission = null): bool
    {
        if (!$this->isPermissionModuleEnabled) {
            return true;
        }
        if ($this->isSuperAdmin()) {
            return true;
        }
        $this->withPermissions();
        $actions = Collection::make();
        $this->getAttribute('permissions')->each(function ($permission) use (&$actions)
        {
            if ($permission->isAction()) {
                [$controller, $action] = explode('@', $permission->permission_mark);
                $actions->add(
                    MModule::getModuleControllerNamespace($permission->module) . ucfirst(
                        $controller
                    ) . 'Controller@' . $action
                );
            }
        });
        if ($permission) {
            [$module, $controller, $action] = explode('@', $permission);
            $permission = MModule::getModuleControllerNamespace($module) . ucfirst(
                    $controller
                ) . 'Controller@' . $action;
        }
        return $actions->contains($permission ? : Route::currentRouteAction());
    }

    /**
     * get RolesModel
     *
     * @see \Xditn\Base\modules\Permissions\Models\Roles
     */
    protected function getRolesModel(): string
    {
        return Roles::class;
    }

    /**
     * get JobsModel
     *
     * @see \Xditn\Base\modules\Permissions\Models\Jobs
     */
    protected function getJobsModel(): string
    {
        return Jobs::class;
    }

    /**
     * get PermissionsModel
     *
     * @see \Xditn\Base\modules\Permissions\Models\Permissions
     */
    protected function getPermissionsModel(): string
    {
        return Permissions::class;
    }
}
