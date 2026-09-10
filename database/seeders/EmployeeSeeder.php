<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\CertificationVerificationStatus;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\LanguageLevel;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\EmployeeWorkload;
use App\Models\Language;
use App\Models\Location;
use App\Models\Position;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $skills = Skill::pluck('id')->all();
        $coreCertificationSlugs = [
            'cisco-ccna', 'cisco-ccnp', 'aws-solutions-architect', 'azure-administrator',
            'certified-information-systems-security-professional', 'certified-scrum-master',
            'pmp', 'oracle-certified-professional', 'google-cloud-professional', 'red-hat-certified-engineer',
        ];
        $coreCertifications = Certification::whereIn('slug', $coreCertificationSlugs)->pluck('id')->all();
        $allLanguages = Language::pluck('id')->all();
        $departments = Department::pluck('id')->all();
        $teams = Team::pluck('id')->all();
        $positions = Position::pluck('id')->all();
        $locations = Location::pluck('id')->all();

        $names = [
            ['Yacine', 'Benali'], ['Amina', 'Zerrouki'], ['Karim', 'Haddadi'], ['Nadia', 'Bouzid'],
            ['Mehdi', 'Cherif'], ['Sofia', 'Meziane'], ['Rachid', 'Kaci'], ['Lamia', 'Touati'],
            ['Omar', 'Slimani'], ['Farida', 'Brahimi'], ['Hocine', 'Mansouri'], ['Salima', 'Ait Ahmed'],
            ['Adel', 'Boukhalfa'], ['Nour', 'Hamidi'], ['Sami', 'Belkacem'], ['Walid', 'Guendouz'],
            ['Imane', 'Saadi'], ['Tarek', 'Boudjemaa'], ['Meriem', 'Haddad'], ['Sofiane', 'Belhadj'],
            ['Lina', 'Kherbache'], ['Riyad', 'Ouchene'], ['Dalila', 'Ferhat'], ['Anis', 'Bekkouche'],
            ['Zahia', 'Mokrani'], ['Fouad', 'Laroussi'], ['Nesrine', 'Boumediene'], ['Slim', 'Cherifi'],
            ['Hanane', 'Tahiri'], ['Aziz', 'Bouziane'],
        ];

        foreach ($names as $index => [$first, $last]) {
            $isActive = $index % 7 !== 6;

            $employee = Employee::create([
                'employee_code' => sprintf('EMP-%03d', $index + 1),
                'first_name' => $first,
                'last_name' => $last,
                'phone' => '+213 '.fake()->unique()->numerify('### ## ## ##'),
                'birth_date' => fake()->dateTimeBetween('-50 years', '-24 years')->format('Y-m-d'),
                'hire_date' => fake()->dateTimeBetween('-12 years', '-6 months')->format('Y-m-d'),
                'employment_type' => fake()->randomElement(EmploymentType::cases()),
                'employment_status' => $isActive ? EmploymentStatus::Active : EmploymentStatus::OnLeave,
                'department_id' => $departments[$index % count($departments)],
                'team_id' => $teams[$index % count($teams)],
                'position_id' => $positions[$index % count($positions)],
                'primary_location_id' => $locations[$index % count($locations)],
                'biography' => fake()->paragraph(2),
            ]);

            foreach ($skills as $skillId) {
                $employee->skills()->attach($skillId, [
                    'proficiency_level' => fake()->randomElement([4, 5]),
                    'years_experience' => fake()->randomFloat(2, 2, 12),
                    'last_used_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    'verified_at' => fake()->boolean(80) ? now()->subDays(fake()->numberBetween(1, 200)) : null,
                ]);
            }

            foreach ($coreCertifications as $certificationId) {
                $employee->certifications()->attach($certificationId, [
                    'certificate_number' => strtoupper((string) fake()->bothify('???-######')),
                    'issued_at' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
                    'verification_status' => CertificationVerificationStatus::Verified,
                ]);
            }

            foreach ($allLanguages as $languageId) {
                $isArabic = $languageId === $allLanguages[0];
                $level = $isArabic ? LanguageLevel::Native : LanguageLevel::Advanced;
                $employee->languages()->attach($languageId, [
                    'speaking_level' => $level,
                    'writing_level' => $level,
                    'reading_level' => $level,
                ]);
            }

            EmployeeAvailability::create([
                'employee_id' => $employee->id,
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->addMonths(6)->format('Y-m-d'),
                'availability_percentage' => $isActive ? fake()->randomElement([50, 75, 100, 100]) : 0,
                'status' => $isActive
                    ? fake()->randomElement([AvailabilityStatus::Available, AvailabilityStatus::PartiallyAvailable])
                    : AvailabilityStatus::OnLeave,
                'reason' => $isActive ? null : 'Planned leave',
            ]);

            EmployeeWorkload::create([
                'employee_id' => $employee->id,
                'period_start' => now()->startOfMonth()->format('Y-m-d'),
                'period_end' => now()->addMonths(6)->format('Y-m-d'),
                'allocated_percentage' => fake()->randomElement([0, 25, 50, 75, 100]),
                'allocated_hours' => fake()->numberBetween(0, 720),
                'source' => 'seed',
            ]);
        }

        Employee::query()
            ->whereNotNull('id')
            ->orderBy('id')
            ->get()
            ->each(function (Employee $employee, $index) {
                $manager = Employee::query()->where('id', '<>', $employee->id)->skip(($index % 5) + 1)->first();
                if ($manager !== null) {
                    $employee->update(['manager_id' => $manager->id]);
                }
            });

        foreach (Department::pluck('id') as $departmentId) {
            $manager = Employee::query()->where('department_id', $departmentId)->first();
            if ($manager) {
                Department::where('id', $departmentId)->update(['manager_id' => $manager->id]);
            }
        }

        foreach (Team::pluck('id') as $teamId) {
            $leader = Employee::query()->where('team_id', $teamId)->first();
            if ($leader) {
                Team::where('id', $teamId)->update(['team_leader_id' => $leader->id]);
            }
        }
    }

    public static function adminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@djezzy.test'],
            [
                'name' => 'Djezzy Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');
    }
}
