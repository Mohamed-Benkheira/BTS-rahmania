<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\BusinessUnit;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Language;
use App\Models\Location;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectEvaluation;
use App\Models\Recommendation;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Team;
use App\Models\User;
use App\Observers\EmployeeObserver;
use App\Observers\ProjectObserver;
use App\Policies\AssignmentPolicy;
use App\Policies\BusinessUnitPolicy;
use App\Policies\CertificationPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\LanguagePolicy;
use App\Policies\LocationPolicy;
use App\Policies\PositionPolicy;
use App\Policies\ProjectCategoryPolicy;
use App\Policies\ProjectEvaluationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RecommendationPolicy;
use App\Policies\RolePolicy;
use App\Policies\SkillCategoryPolicy;
use App\Policies\SkillPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate as GateFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
        $this->registerObservers();
    }

    /**
     * Register model observers for audit logging.
     */
    protected function registerObservers(): void
    {
        Employee::observe(EmployeeObserver::class);
        Project::observe(ProjectObserver::class);
    }

    /**
     * Register model policies and grant the super-admin role full access.
     */
    protected function registerPolicies(): void
    {
        GateFacade::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        GateFacade::policy(BusinessUnit::class, BusinessUnitPolicy::class);
        GateFacade::policy(Department::class, DepartmentPolicy::class);
        GateFacade::policy(Team::class, TeamPolicy::class);
        GateFacade::policy(Position::class, PositionPolicy::class);
        GateFacade::policy(Location::class, LocationPolicy::class);
        GateFacade::policy(Employee::class, EmployeePolicy::class);
        GateFacade::policy(Skill::class, SkillPolicy::class);
        GateFacade::policy(SkillCategory::class, SkillCategoryPolicy::class);
        GateFacade::policy(Certification::class, CertificationPolicy::class);
        GateFacade::policy(Language::class, LanguagePolicy::class);
        GateFacade::policy(Project::class, ProjectPolicy::class);
        GateFacade::policy(ProjectCategory::class, ProjectCategoryPolicy::class);
        GateFacade::policy(Assignment::class, AssignmentPolicy::class);
        GateFacade::policy(ProjectEvaluation::class, ProjectEvaluationPolicy::class);
        GateFacade::policy(Recommendation::class, RecommendationPolicy::class);
        GateFacade::policy(User::class, UserPolicy::class);
        GateFacade::policy(Role::class, RolePolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
