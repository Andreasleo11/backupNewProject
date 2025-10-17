<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Domain\Signature\Entities\UserSignature as DomainUserSignature;
use App\Policies\SignaturePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DomainUserSignature::class => SignaturePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('approve-requirements', function ($user) {
            //   return method_exists($user, 'hasRoles')
            //     ? $user->hasRole('Admin')
            //     : in_array($user->email, ['yuli@daijo.co.id', 'raymond@daijo.co.id']);
            return $user->role->name === 'SUPERADMIN' || in_array($user->email, ['yuli@daijo.co.id']);
        });
        $this->registerPolicies();
        Gate::define('manage-approvals', function ($user) {
            // adjust to your roles/permissions system
            return $user->role->name === 'SUPERADMIN' || $user->can('manage-approvals');
        });
    }
}
