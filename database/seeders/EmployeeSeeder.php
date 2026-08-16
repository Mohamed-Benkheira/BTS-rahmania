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
        $certifications = Certification::pluck('id')->all();
        $languages = Language::pluck('id')->all();
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

            $employee->skills()->attach(fake()->randomElements($skills, fake()->numberBetween(3, 6)), [
                'proficiency_level' => fake()->numberBetween(2, 5),
                'years_experience' => fake()->randomFloat(2, 1, 12),
                'last_used_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'verified_at' => fake()->boolean(60) ? now()->subDays(fake()->numberBetween(1, 200)) : null,
            ]);

            $employee->certifications()->attach(fake()->randomElements($certifications, fake()->numberBetween(1, 3)), [
                'certificate_number' => strtoupper((string) fake()->bothify('???-######')),
                'issued_at' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                'verification_status' => fake()->randomElement([
                    CertificationVerificationStatus::Verified,
                    CertificationVerificationStatus::Verified,
                    CertificationVerificationStatus::Pending,
                ]),
            ]);

            $employee->languages()->attach($languages[0], [
                'speaking_level' => LanguageLevel::Native,
                'writing_level' => LanguageLevel::Native,
                'reading_level' => LanguageLevel::Native,
            ]);

            $employee->languages()->attach(fake()->randomElements(array_slice($languages, 1), fake()->numberBetween(1, 2)), [
                'speaking_level' => fake()->randomElement([LanguageLevel::Intermediate, LanguageLevel::Advanced]),
                'writing_level' => fake()->randomElement([LanguageLevel::Intermediate, LanguageLevel::Advanced]),
                'reading_level' => fake()->randomElement([LanguageLevel::Intermediate, LanguageLevel::Advanced]),
            ]);

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
