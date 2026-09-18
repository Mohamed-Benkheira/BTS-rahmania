<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\CertificationVerificationStatus;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\LanguageLevel;
use App\Models\BusinessUnit;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    public const EMPLOYEE_COUNT = 300;

    /** Skills (by name) every breadth champion is guaranteed to hold. */
    public const BREADTH_SKILL_NAMES = [
        '4G/5G Core', 'TCP/IP', 'SQL', 'Python', 'Cloud Architecture',
        'Agile Methodologies', 'Project Management', 'Requirements Analysis',
    ];

    /** Certifications (by name) every breadth champion is guaranteed to hold. */
    public const BREADTH_CERT_NAMES = [
        'Cisco CCNA', 'AWS Solutions Architect', 'Certified Scrum Master',
        'Azure Administrator', 'PMP',
    ];

    /** @var array<string, string> email => employee_code reserved for named accounts. */
    protected static array $reservedEmployeeCodes = [];

    /** @var array<int, int> */
    protected static array $breadthEmployeeIds = [];

    protected bool $hrReserved = false;

    protected bool $itReserved = false;

    protected bool $pmReserved = false;

    public static function reservedLinks(): array
    {
        return self::$reservedEmployeeCodes;
    }

    /** @return array<int, int> */
    public static function breadthIds(): array
    {
        return self::$breadthEmployeeIds;
    }

    public static function adminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'djezzy.test@admin.invalid'],
            ['name' => 'Djezzy Administrator'],
        );

        if ($admin->wasRecentlyCreated) {
            $admin->forceFill([
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();
        }

        $admin->assignRole('super-admin');
    }

    public function run(): void
    {
        self::$reservedEmployeeCodes = [];
        self::$breadthEmployeeIds = [];
        $this->hrReserved = false;
        $this->itReserved = false;
        $this->pmReserved = false;

        $departments = Department::query()->get()->keyBy('code');
        $teamsByDepartment = Team::query()->get()->groupBy('department_id');
        $skills = Skill::query()->get()->keyBy('name');
        $certifications = Certification::query()->get()->keyBy('name');
        $languages = Language::query()->get()->keyBy('code');
        $locations = Location::query()->pluck('id')->all();

        $employeeRoleId = Role::query()->where('name', 'employee')->value('id');
        $adminUserId = User::query()->where('email', 'admin@djezzy.test')->value('id');

        $deployment = $this->departmentDeployment();
        $skillPools = $this->departmentSkillPools();
        $names = $this->namePool();

        $passwordHash = Hash::make('password');

        $counter = 0;
        $employees = [];

        foreach ($deployment as $departmentCode => $count) {
            $department = $departments->get($departmentCode);
            $departmentTeams = $teamsByDepartment->get($department->id, collect())->values();
            $pool = $this->weightedPositions($departmentCode);

            $deptEmployees = [];

            for ($i = 0; $i < $count; $i++) {
                $counter++;
                $position = $pool[$i % count($pool)];
                $team = $departmentTeams->isEmpty() ? null : $departmentTeams[$i % $departmentTeams->count()];
                [$firstName, $lastName] = $names[$counter - 1];

                $fullName = "{$firstName} {$lastName}";
                $isReserved = $this->isReservedAccount($counter, $departmentCode, $position);

                $employee = Employee::create([
                    'user_id' => $isReserved ? null : $this->createAccount($fullName, $this->employeeEmail($firstName, $lastName, $counter), $passwordHash, $employeeRoleId),
                    'employee_code' => sprintf('EMP-%03d', $counter),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => '+213 '.fake()->numerify('5#########'),
                    'birth_date' => fake()->dateTimeBetween('-52 years', '-24 years')->format('Y-m-d'),
                    'hire_date' => $this->hireDate($counter),
                    'employment_type' => $counter % 14 === 0 ? EmploymentType::Contractor : (fake()->randomElement([EmploymentType::FullTime, EmploymentType::FullTime, EmploymentType::FullTime, EmploymentType::PartTime])),
                    'employment_status' => $this->employmentStatus($counter),
                    'business_unit_id' => $department->business_unit_id,
                    'department_id' => $department->id,
                    'team_id' => $team?->id,
                    'position_id' => $position['id'],
                    'primary_location_id' => $locations[$counter % count($locations)],
                    'biography' => fake()->paragraph(2),
                ]);

                if ($isReserved) {
                    $this->rememberReservedAccount($employee);
                }

                $deptEmployees[] = $employee;
                $employees[] = $employee;

                $this->addSkills($employee, $skillPools[$departmentCode], $position, $adminUserId);
                $this->addCertifications($employee, $certifications, $adminUserId);
                $this->addLanguages($employee, $languages, $departmentCode);
                $this->addAvailability($employee);
                $this->addWorkload($employee);
            }

            $this->wireHierarchy($deptEmployees);
        }

        $this->designateBreadthChampions($employees, $skills, $certifications, $languages, $adminUserId);

        $this->wireTeamsAndDepartments($employees);

        $this->seedNearExpiryCertifications($employees);
    }

    /** @return array<string, int> */
    private function departmentDeployment(): array
    {
        return [
            'DEPT-RETAIL' => 36,
            'DEPT-DISTRIBUTION' => 10,
            'DEPT-MARKETING' => 12,
            'DEPT-CARE' => 36,
            'DEPT-ENT-SALES' => 14,
            'DEPT-KEY-ACC' => 8,
            'DEPT-SOLUTIONS' => 10,
            'DEPT-IOT' => 8,
            'DEPT-CORE-NET' => 14,
            'DEPT-RAN' => 22,
            'DEPT-NET-SEC' => 10,
            'DEPT-IT' => 26,
            'DEPT-DIGITAL' => 14,
            'DEPT-DATA' => 10,
            'DEPT-ARCH' => 8,
            'DEPT-QA' => 12,
            'DEPT-FINANCE' => 12,
            'DEPT-HR' => 12,
            'DEPT-PROC' => 10,
            'DEPT-LEGAL' => 6,
            'DEPT-COMMS' => 6,
            'DEPT-STRATEGY' => 4,
        ];
    }

    /**
     * Position pools per department. Weights control how often a position is
     * assigned; each pool contains at least one manager/director level role.
     *
     * @return array<int, array{id: int, code: string, level: ?string}>
     */
    private function weightedPositions(string $departmentCode): array
    {
        $pools = [
            'DEPT-RETAIL' => ['POS-STM' => 2, 'POS-SR-CSA' => 6, 'POS-RA' => 22, 'POS-SALES' => 6],
            'DEPT-DISTRIBUTION' => ['POS-SCA' => 3, 'POS-SALES' => 4, 'POS-RA' => 2, 'POS-TL' => 1],
            'DEPT-MARKETING' => ['POS-BRAND' => 1, 'POS-MKT' => 7, 'POS-MRA' => 3, 'POS-UX' => 1],
            'DEPT-CARE' => ['POS-CC-SUP' => 2, 'POS-SR-CSA' => 8, 'POS-CSA' => 26],
            'DEPT-ENT-SALES' => ['POS-AM' => 4, 'POS-SALES' => 6, 'POS-BA' => 2, 'POS-TL' => 2],
            'DEPT-KEY-ACC' => ['POS-KAM' => 3, 'POS-AM' => 3, 'POS-BA' => 2],
            'DEPT-SOLUTIONS' => ['POS-BA' => 3, 'POS-NET-ENG' => 3, 'POS-SWE' => 2, 'POS-PM' => 1, 'POS-DA' => 1],
            'DEPT-IOT' => ['POS-NET-ENG' => 3, 'POS-SWE' => 2, 'POS-DE' => 1, 'POS-BA' => 1, 'POS-PDM' => 1],
            'DEPT-CORE-NET' => ['POS-NET-ARCH' => 1, 'POS-SR-NET' => 4, 'POS-NET-ENG' => 6, 'POS-FIELD-ENG' => 2, 'POS-DEVOPS' => 1],
            'DEPT-RAN' => ['POS-RAD-ENG' => 6, 'POS-SR-RAD' => 4, 'POS-FIELD-ENG' => 10, 'POS-TL' => 1, 'POS-NET-ENG' => 1],
            'DEPT-NET-SEC' => ['POS-SEC-ENG' => 3, 'POS-SOC' => 4, 'POS-SR-NET' => 1, 'POS-DEVOPS' => 1, 'POS-TL' => 1],
            'DEPT-IT' => ['POS-SWE' => 8, 'POS-SR-SWE' => 5, 'POS-LEAD-SWE' => 1, 'POS-DEVOPS' => 4, 'POS-DBA' => 3, 'POS-BA' => 2, 'POS-UX' => 1, 'POS-SOC' => 1, 'POS-TL' => 1],
            'DEPT-DIGITAL' => ['POS-SWE' => 5, 'POS-SR-SWE' => 2, 'POS-PDM' => 2, 'POS-UX' => 2, 'POS-LEAD-SWE' => 1, 'POS-QA' => 1, 'POS-QA-ENG' => 1],
            'DEPT-DATA' => ['POS-DE' => 3, 'POS-DA' => 3, 'POS-DS' => 1, 'POS-DBA' => 1, 'POS-BA' => 1, 'POS-TL' => 1],
            'DEPT-ARCH' => ['POS-EA' => 1, 'POS-LEAD-SWE' => 2, 'POS-SR-SWE' => 2, 'POS-SR-NET' => 1, 'POS-DS' => 1, 'POS-DEVOPS' => 1],
            'DEPT-QA' => ['POS-QA' => 6, 'POS-QA-ENG' => 4, 'POS-BA' => 1, 'POS-TL' => 1],
            'DEPT-FINANCE' => ['POS-FC' => 1, 'POS-SR-FA' => 3, 'POS-FA' => 5, 'POS-ACC' => 3],
            'DEPT-HR' => ['POS-HRBP' => 4, 'POS-TA' => 4, 'POS-HRA' => 3, 'POS-TL' => 1],
            'DEPT-PROC' => ['POS-PROC' => 5, 'POS-SCA' => 4, 'POS-TL' => 1],
            'DEPT-LEGAL' => ['POS-LGL' => 3, 'POS-COC' => 2, 'POS-TL' => 1],
            'DEPT-COMMS' => ['POS-COO' => 3, 'POS-MKT' => 2, 'POS-TL' => 1],
            'DEPT-STRATEGY' => ['POS-PM' => 2, 'POS-BA' => 1, 'POS-DA' => 1],
        ];

        $positions = Position::whereIn('code', array_keys($pools[$departmentCode]))->get()->keyBy('code');

        $weighted = [];
        foreach ($pools[$departmentCode] as $code => $weight) {
            $position = $positions->get($code);
            if ($position === null) {
                continue;
            }
            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = ['id' => $position->id, 'code' => $code, 'level' => $position->level];
            }
        }

        return $weighted;
    }

    /** @return array<string, array<int, string>> */
    private function departmentSkillPools(): array
    {
        return [
            'DEPT-RETAIL' => ['Retail Operations', 'Customer Experience Management', 'Customer Relationship Management', 'Negotiation', 'B2B Sales', 'Campaign Management'],
            'DEPT-DISTRIBUTION' => ['Retail Operations', 'B2B Sales', 'Negotiation', 'Customer Relationship Management', 'Excel'],
            'DEPT-MARKETING' => ['Digital Marketing', 'Content Creation', 'SEO/SEM', 'Brand Strategy', 'Campaign Management', 'Market Research', 'Statistics', 'Excel'],
            'DEPT-CARE' => ['Customer Experience Management', 'Customer Relationship Management', 'Call Center Technology (CTI)', 'Negotiation', 'VoIP', 'Excel'],
            'DEPT-ENT-SALES' => ['B2B Sales', 'Negotiation', 'Customer Relationship Management', 'Requirements Analysis', 'Stakeholder Management', 'Financial Analysis', 'Excel'],
            'DEPT-KEY-ACC' => ['B2B Sales', 'Negotiation', 'Customer Relationship Management', 'Roaming Operations', 'Risk Management', 'Stakeholder Management', 'Financial Analysis'],
            'DEPT-SOLUTIONS' => ['Requirements Analysis', 'Project Management', 'Stakeholder Management', 'Business Process Modeling', 'Agile Methodologies', 'TCP/IP', 'MPLS', 'Cloud Architecture'],
            'DEPT-IOT' => ['TCP/IP', 'MPLS', '4G/5G Core', 'Cloud Architecture', 'Requirements Analysis', 'Data Modeling', 'Python'],
            'DEPT-CORE-NET' => ['TCP/IP', 'MPLS', '4G/5G Core', 'Routing & Switching', 'Cisco IOS', 'SS7/Diameter', 'SDN/NFV', 'Optical Transport (DWDM)', 'VoIP', 'Network Monitoring (NOC)', 'Linux Administration'],
            'DEPT-RAN' => ['Radio Access Networks', 'RF Planning', 'Antenna Systems', 'Fiber Optics', '4G/5G Core', 'TCP/IP', 'Network Monitoring (NOC)', 'Cisco IOS'],
            'DEPT-NET-SEC' => ['Information Security', 'SOC Analysis', 'Penetration Testing', 'IAM', 'Incident Response', 'Security Auditing', 'Firewalls', 'Linux Administration', 'TCP/IP'],
            'DEPT-IT' => ['PHP', 'Laravel', 'Python', 'Java', 'JavaScript', 'TypeScript', 'React', 'Node.js', 'SQL', 'PostgreSQL', 'Redis', 'Docker', 'Kubernetes', 'CI/CD', 'Linux Administration', 'Terraform', 'Cloud Architecture', 'Agile Methodologies', 'Project Management', 'Technical Writing'],
            'DEPT-DIGITAL' => ['PHP', 'Laravel', 'React', 'TypeScript', 'Node.js', 'Python', 'SQL', 'PostgreSQL', 'Redis', 'UI/UX Design', 'Agile Methodologies', 'Project Management', 'Digital Marketing', 'Mobile Payment'],
            'DEPT-DATA' => ['Python', 'SQL', 'PostgreSQL', 'Data Modeling', 'ETL', 'Business Intelligence', 'Power BI', 'Tableau', 'Machine Learning', 'Statistics', 'Big Data (Hadoop)', 'Excel'],
            'DEPT-ARCH' => ['Cloud Architecture', 'Agile Methodologies', 'Business Process Modeling', 'Risk Management', 'Requirements Analysis', 'TCP/IP', 'Data Modeling', 'Security Auditing'],
            'DEPT-QA' => ['Python', 'JavaScript', 'SQL', 'Agile Methodologies', 'Technical Writing', 'Business Process Modeling', 'Workforce Planning'],
            'DEPT-FINANCE' => ['Financial Analysis', 'Budgeting', 'Excel', 'Statistics', 'Power BI', 'Risk Management', 'Technical Writing'],
            'DEPT-HR' => ['Workforce Planning', 'Coaching & Mentoring', 'Change Management', 'Project Management', 'Stakeholder Management', 'Business Process Modeling', 'Excel', 'Technical Writing'],
            'DEPT-PROC' => ['Negotiation', 'Budgeting', 'Risk Management', 'Excel', 'Business Process Modeling', 'Financial Analysis', 'Technical Writing'],
            'DEPT-LEGAL' => ['Negotiation', 'Risk Management', 'Technical Writing', 'Business Process Modeling', 'Stakeholder Management'],
            'DEPT-COMMS' => ['Content Creation', 'Public Relations', 'Digital Marketing', 'Campaign Management', 'Technical Writing', 'Brand Strategy', 'Market Research'],
            'DEPT-STRATEGY' => ['Market Research', 'Financial Analysis', 'Statistics', 'Excel', 'Risk Management', 'Stakeholder Management'],
        ];
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function namePool(): array
    {
        $first = explode(' ', 'Yacine Amina Karim Nadia Mehdi Sofia Rachid Lamia Omar Farida Hocine Salima Adel Nour Sami Walid Imane Tarek Meriem Sofiane Lina Riyad Dalila Anis Zahia Fouad Nesrine Slim Hanane Aziz Sonia Reda Nassim Hayet Djamel Rima Samir Khadija Mounir Yasmine Bilal Amel Ramy Selma Zaki Nora Adelina Mourad Lynda Hakim Cherifa Idir Fazia Nacera Brahim Malika Toufik Ouardia Salem Assia Riad Nassima Karima Djamelia');
        $last = explode(' ', 'Benali Zerrouki Haddadi Bouzid Cherif Meziane Kaci Touati Slimani Brahimi Mansouri Ait Ahmed Boukhalfa Hamidi Belkacem Guendouz Saadi Boudjemaa Haddad Belhadj Kherbache Ouchene Ferhat Bekkouche Mokrani Laroussi Boumediene Cherifi Tahiri Bouziane Amrani Bensalem Guechi Rezgui Merabet Younsi Djeffal Sellami Taleb Boudiaf Zaidi Aouali Messaoud Henni Fergani Osmani Meddour Gharbi Chaabane Belkadi Rahmani Benyahia Allal Mansour Hammadi Derradji Ould Amara');

        $names = [];
        for ($i = 0; $i < self::EMPLOYEE_COUNT; $i++) {
            $names[$i] = [$first[$i % count($first)], $last[intdiv($i, count($first)) % count($last)]];
        }

        return $names;
    }

    private function employeeEmail(string $firstName, string $lastName, int $counter): string
    {
        $base = Str::lower(Str::slug($firstName.' '.$lastName, '.'));

        return "{$base}.{$counter}@djezzy.dz";
    }

    private function createAccount(string $name, string $email, string $passwordHash, ?int $employeeRoleId): int
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'email_verified_at' => now(),
            'is_active' => true,
            'last_login_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-45 days', 'now') : null,
        ]);

        if ($employeeRoleId !== null) {
            \DB::table('model_has_roles')->insert([
                'role_id' => $employeeRoleId,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }

        return $user->id;
    }

    private function hireDate(int $counter): string
    {
        return (match (true) {
            $counter % 37 === 0 => fake()->dateTimeBetween('-1 year', '-3 months'),
            default => fake()->dateTimeBetween('-24 years', '-6 months'),
        })->format('Y-m-d');
    }

    private function employmentStatus(int $counter): EmploymentStatus
    {
        return match ($counter % 20) {
            0 => EmploymentStatus::OnLeave,
            1 => EmploymentStatus::Resigned,
            2 => EmploymentStatus::Retired,
            default => EmploymentStatus::Active,
        };
    }

    /**
     * @param  array{id: int, code: string, level: ?string}  $position
     */
    private function isReservedAccount(int $counter, string $departmentCode, array $position): bool
    {
        if ($counter === 1) {
            return true; // employee@example.com
        }

        if (! $this->hrReserved && $departmentCode === 'DEPT-HR' && $position['level'] === 'manager') {
            $this->hrReserved = true;

            return true;
        }

        if (! $this->itReserved && $departmentCode === 'DEPT-IT') {
            $this->itReserved = true;

            return true;
        }

        if (! $this->pmReserved && $departmentCode !== 'DEPT-HR' && $position['code'] === 'POS-PM') {
            $this->pmReserved = true;

            return true;
        }

        return false;
    }

    private function rememberReservedAccount(Employee $employee): void
    {
        $departmentCode = $employee->department?->code;
        $positionCode = $employee->position?->code;

        if ($employee->employee_code === 'EMP-001') {
            self::$reservedEmployeeCodes['employee@example.com'] = $employee->employee_code;
        } elseif (! isset(self::$reservedEmployeeCodes['hr@example.com']) && $departmentCode === 'DEPT-HR') {
            self::$reservedEmployeeCodes['hr@example.com'] = $employee->employee_code;
        } elseif (! isset(self::$reservedEmployeeCodes['resources@example.com']) && $departmentCode === 'DEPT-IT') {
            self::$reservedEmployeeCodes['resources@example.com'] = $employee->employee_code;
        } elseif (! isset(self::$reservedEmployeeCodes['projects@example.com']) && $positionCode === 'POS-PM') {
            self::$reservedEmployeeCodes['projects@example.com'] = $employee->employee_code;
        }
    }

    /**
     * @param  array<int, string>  $pool
     * @param  array{id: int, code: string, level: ?string}  $position
     */
    private function addSkills(Employee $employee, array $pool, array $position, ?int $verifiedBy): void
    {
        $slugs = array_map(static fn (string $name): string => Str::slug($name), $pool);
        $skills = Skill::whereIn('slug', $slugs)->get();

        if ($skills->isEmpty()) {
            return;
        }

        $count = fake()->numberBetween(6, min(13, $skills->count()));
        $chosen = $skills->count() > $count ? $skills->shuffle()->take($count) : $skills;

        $proficiencyRange = match ($position['level']) {
            'junior' => [2, 3],
            'senior', 'manager', 'director' => [4, 5],
            default => [3, 4],
        };

        foreach ($chosen as $skill) {
            $employee->skills()->attach($skill->id, [
                'proficiency_level' => fake()->numberBetween($proficiencyRange[0], $proficiencyRange[1]),
                'years_experience' => fake()->randomFloat(1, 1, 12),
                'last_used_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'verified_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(1, 300)) : null,
                'verified_by' => $verifiedBy,
                'notes' => null,
            ]);
        }
    }

    private function addCertifications(Employee $employee, Collection $certifications, ?int $verifiedBy): void
    {
        $count = fake()->numberBetween(0, 3);
        if ($count === 0 || $certifications->isEmpty()) {
            return;
        }

        $chosen = $certifications->shuffle()->take($count);

        foreach ($chosen as $certification) {
            $issuedAt = fake()->dateTimeBetween('-4 years', '-4 months');
            $valid = (clone $issuedAt)->modify('+'.($certification->validity_period_months ?? 36).' months');

            $employee->certifications()->attach($certification->id, [
                'certificate_number' => strtoupper((string) fake()->bothify('???-#######')),
                'issued_at' => $issuedAt->format('Y-m-d'),
                'expires_at' => $certification->validity_period_months ? $valid->format('Y-m-d') : null,
                'document_path' => null,
                'verification_status' => fake()->randomElement([
                    CertificationVerificationStatus::Verified->value,
                    CertificationVerificationStatus::Verified->value,
                    CertificationVerificationStatus::Verified->value,
                    CertificationVerificationStatus::Pending->value,
                    CertificationVerificationStatus::Rejected->value,
                ]),
            ]);
        }

        unset($verifiedBy);
    }

    private function addLanguages(Employee $employee, Collection $languages, string $departmentCode): void
    {
        $arabic = $languages->get('ar');
        $french = $languages->get('fr');
        $english = $languages->get('en');

        $corporate = in_array($departmentCode, ['DEPT-FINANCE', 'DEPT-HR', 'DEPT-LEGAL', 'DEPT-COMMS', 'DEPT-STRATEGY', 'DEPT-PROC'], true);

        $attach = static function (Language $language, LanguageLevel $level) use ($employee): void {
            $employee->languages()->attach($language->id, [
                'speaking_level' => $level,
                'writing_level' => $level,
                'reading_level' => $level,
            ]);
        };

        if ($arabic) {
            $attach($arabic, LanguageLevel::Native);
        }
        if ($french) {
            $attach($french, $corporate ? LanguageLevel::Native : (fake()->boolean(60) ? LanguageLevel::Advanced : LanguageLevel::Native));
        }
        if ($english) {
            $attach($english, fake()->randomElement([LanguageLevel::Intermediate, LanguageLevel::Advanced]));
        }

        $extra = $languages->filter(fn (Language $l) => ! in_array($l->code, ['ar', 'fr', 'en'], true));
        if ($extra->isNotEmpty() && fake()->boolean(35)) {
            $attach($extra->random(), LanguageLevel::Intermediate);
        }
    }

    private function addAvailability(Employee $employee): void
    {
        $onLeave = $employee->employment_status === EmploymentStatus::OnLeave;
        $percentage = $onLeave ? 0 : fake()->randomElement([60, 75, 90, 100, 100, 100]);

        EmployeeAvailability::create([
            'employee_id' => $employee->id,
            'start_date' => now()->subMonths(9)->format('Y-m-d'),
            'end_date' => now()->addMonths(fake()->numberBetween(6, 12))->format('Y-m-d'),
            'availability_percentage' => $percentage,
            'status' => $onLeave ? AvailabilityStatus::OnLeave : ($percentage >= 100 ? AvailabilityStatus::Available : AvailabilityStatus::PartiallyAvailable),
            'reason' => $onLeave ? fake()->randomElement(['Maternity leave', 'Sabbatical', 'Medical leave', 'Planned vacation']) : null,
            'notes' => null,
        ]);

        if (fake()->boolean(70)) {
            EmployeeAvailability::create([
                'employee_id' => $employee->id,
                'start_date' => now()->subMonths(30)->addMonths(fake()->numberBetween(0, 6))->format('Y-m-d'),
                'end_date' => now()->subMonths(10)->format('Y-m-d'),
                'availability_percentage' => fake()->randomElement([30, 50, 75, 100]),
                'status' => fake()->randomElement([AvailabilityStatus::Available, AvailabilityStatus::PartiallyAvailable]),
                'reason' => null,
                'notes' => null,
            ]);
        }
    }

    private function addWorkload(Employee $employee): void
    {
        $onLeave = $employee->employment_status === EmploymentStatus::OnLeave;
        $allocated = $onLeave ? 0 : fake()->randomElement([0, 10, 10, 20, 25]);

        EmployeeWorkload::create([
            'employee_id' => $employee->id,
            'period_start' => now()->subMonths(2)->format('Y-m-d'),
            'period_end' => now()->addMonths(6)->format('Y-m-d'),
            'allocated_percentage' => $allocated,
            'allocated_hours' => (int) round($allocated * 4 * 4.34),
            'source' => 'seed',
        ]);

        EmployeeWorkload::create([
            'employee_id' => $employee->id,
            'period_start' => now()->subMonths(14)->format('Y-m-d'),
            'period_end' => now()->subMonths(3)->format('Y-m-d'),
            'allocated_percentage' => fake()->randomElement([0, 25, 50]),
            'allocated_hours' => fake()->numberBetween(0, 400),
            'source' => 'seed',
        ]);
    }

    /** @param  array<int, Employee>  $deptEmployees */
    private function wireHierarchy(array $deptEmployees): void
    {
        if (count($deptEmployees) <= 1) {
            return;
        }

        $leaderRank = static fn (Employee $e): int => in_array($e->position?->level, ['director', 'manager', 'senior'], true) ? 0 : 1;

        $head = collect($deptEmployees)->sortBy($leaderRank)->first();

        foreach ($deptEmployees as $employee) {
            if ($employee->id === $head->id) {
                continue;
            }
            $employee->update(['manager_id' => $head->id]);
        }
    }

    /**
     * @param  array<int, Employee>  $employees
     */
    private function designateBreadthChampions(array $employees, Collection $skills, Collection $certifications, Collection $languages, ?int $verifiedBy): void
    {
        $breadthDepartments = [
            'DEPT-IT', 'DEPT-DIGITAL', 'DEPT-DATA', 'DEPT-ARCH',
            'DEPT-QA', 'DEPT-SOLUTIONS', 'DEPT-CORE-NET', 'DEPT-NET-SEC',
        ];

        $champions = collect($employees)
            ->filter(fn (Employee $e) => $e->employment_status === EmploymentStatus::Active)
            ->filter(fn (Employee $e) => in_array($e->department?->code, $breadthDepartments, true))
            ->take(16)
            ->values();

        self::$breadthEmployeeIds = $champions->pluck('id')->all();

        foreach ($champions as $champion) {
            foreach (self::BREADTH_SKILL_NAMES as $skillName) {
                $skill = $skills->get($skillName);
                if ($skill !== null && ! $champion->skills()->wherePivot('skill_id', $skill->id)->exists()) {
                    $champion->skills()->attach($skill->id, [
                        'proficiency_level' => 4,
                        'years_experience' => fake()->randomFloat(1, 4, 10),
                        'last_used_at' => now()->toDateString(),
                        'verified_at' => now()->subDays(30),
                        'verified_by' => $verifiedBy,
                        'notes' => 'Core competency (platform champion).',
                    ]);
                }
            }

            foreach (self::BREADTH_CERT_NAMES as $certName) {
                $certification = $certifications->get($certName);
                if ($certification === null || $champion->certifications()->wherePivot('certification_id', $certification->id)->exists()) {
                    continue;
                }

                $validity = $certification->validity_period_months ?? 36;
                $expiresAt = now()->addMonths(fake()->numberBetween(6, 18));
                $issuedAt = (clone $expiresAt)->subMonths($validity);

                $champion->certifications()->attach($certification->id, [
                    'certificate_number' => strtoupper((string) fake()->bothify('???-#######')),
                    'issued_at' => $issuedAt->format('Y-m-d'),
                    'expires_at' => $expiresAt->format('Y-m-d'),
                    'document_path' => null,
                    'verification_status' => CertificationVerificationStatus::Verified->value,
                ]);
            }

            $french = $languages->get('fr');
            $english = $languages->get('en');

            foreach ([$french, $english] as $language) {
                if ($language !== null && ! $champion->languages()->wherePivot('language_id', $language->id)->exists()) {
                    $champion->languages()->attach($language->id, [
                        'speaking_level' => LanguageLevel::Advanced,
                        'writing_level' => LanguageLevel::Advanced,
                        'reading_level' => LanguageLevel::Advanced,
                    ]);
                }
            }
        }
    }

    /** @param  array<int, Employee>  $employees */
    private function wireTeamsAndDepartments(array $employees): void
    {
        $ordered = collect($employees)->sortBy('id');

        foreach (Department::all() as $department) {
            $members = $ordered->where('department_id', $department->id);
            $manager = $members
                ->sortBy(fn (Employee $e) => ! in_array($e->position?->level, ['manager', 'director'], true))
                ->first();

            if ($manager) {
                Department::whereKey($department->id)->update(['manager_id' => $manager->id]);
            }
        }

        foreach (Team::all() as $team) {
            $members = $ordered->where('team_id', $team->id);
            $leader = $members
                ->sortBy(fn (Employee $e) => ! in_array($e->position?->level, ['manager', 'senior', 'director'], true))
                ->first();

            if ($leader) {
                Team::whereKey($team->id)->update(['team_leader_id' => $leader->id]);
            }
        }

        foreach (BusinessUnit::all() as $businessUnit) {
            $members = $ordered->where('business_unit_id', $businessUnit->id);
            $director = $members
                ->sortBy(fn (Employee $e) => ! in_array($e->position?->level, ['director'], true))
                ->first();

            if ($director) {
                BusinessUnit::whereKey($businessUnit->id)->update(['manager_id' => $director->id]);
            }
        }
    }

    /** @param  array<int, Employee>  $employees */
    private function seedNearExpiryCertifications(array $employees): void
    {
        $candidates = collect($employees)
            ->filter(fn (Employee $e) => ! in_array($e->id, self::$breadthEmployeeIds, true))
            ->shuffle()
            ->take(18);

        foreach ($candidates as $employee) {
            $certification = $employee->certifications()
                ->wherePivot('verification_status', CertificationVerificationStatus::Verified->value)
                ->get()
                ->first(fn ($cert) => $cert->pivot->expires_at !== null);

            if ($certification === null) {
                continue;
            }

            $employee->certifications()->updateExistingPivot($certification->id, [
                'expires_at' => now()->addDays(fake()->numberBetween(10, 55))->toDateString(),
            ]);
        }
    }
}
