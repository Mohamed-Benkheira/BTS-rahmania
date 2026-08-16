<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Location;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $businessUnits = [
            ['name' => 'Consumer Mobile', 'code' => 'BU-CONSUMER'],
            ['name' => 'Enterprise & B2B', 'code' => 'BU-ENTERPRISE'],
            ['name' => 'Network & Infrastructure', 'code' => 'BU-NETWORK'],
            ['name' => 'Corporate Services', 'code' => 'BU-CORPORATE'],
        ];

        foreach ($businessUnits as $unit) {
            BusinessUnit::firstOrCreate(['code' => $unit['code']], $unit);
        }

        $businessUnitId = fn (string $code): int => BusinessUnit::where('code', $code)->value('id');

        $departments = [
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Retail Operations', 'code' => 'DEPT-RETAIL'],
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Marketing', 'code' => 'DEPT-MARKETING'],
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Customer Service', 'code' => 'DEPT-CARE'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'Enterprise Sales', 'code' => 'DEPT-ENT-SALES'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'Solutions Engineering', 'code' => 'DEPT-SOLUTIONS'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'Core Network', 'code' => 'DEPT-CORE-NET'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'IT & Digital', 'code' => 'DEPT-IT'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Finance', 'code' => 'DEPT-FINANCE'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Human Resources', 'code' => 'DEPT-HR'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], [
                'business_unit_id' => $businessUnitId($department['business_unit_code']),
                'name' => $department['name'],
            ]);
        }

        $departmentId = fn (string $code): int => Department::where('code', $code)->value('id');

        $teams = [
            ['department_code' => 'DEPT-RETAIL', 'name' => 'Store Operations Team', 'code' => 'TEAM-STORE-OPS'],
            ['department_code' => 'DEPT-MARKETING', 'name' => 'Digital Marketing Team', 'code' => 'TEAM-DIGITAL-MKT'],
            ['department_code' => 'DEPT-CARE', 'name' => 'Call Center Team', 'code' => 'TEAM-CALL-CENTER'],
            ['department_code' => 'DEPT-ENT-SALES', 'name' => 'Key Accounts Team', 'code' => 'TEAM-KEY-ACCOUNTS'],
            ['department_code' => 'DEPT-SOLUTIONS', 'name' => 'Pre-Sales Team', 'code' => 'TEAM-PRE-SALES'],
            ['department_code' => 'DEPT-CORE-NET', 'name' => 'Radio Access Team', 'code' => 'TEAM-RAN'],
            ['department_code' => 'DEPT-IT', 'name' => 'Platform Engineering Team', 'code' => 'TEAM-PLATFORM'],
            ['department_code' => 'DEPT-FINANCE', 'name' => 'Financial Planning Team', 'code' => 'TEAM-FP'],
        ];

        foreach ($teams as $team) {
            Team::firstOrCreate(['code' => $team['code']], [
                'department_id' => $departmentId($team['department_code']),
                'name' => $team['name'],
            ]);
        }

        $positions = [
            ['title' => 'Software Engineer', 'code' => 'POS-SWE', 'level' => 'mid'],
            ['title' => 'Senior Software Engineer', 'code' => 'POS-SR-SWE', 'level' => 'senior'],
            ['title' => 'Network Engineer', 'code' => 'POS-NET-ENG', 'level' => 'mid'],
            ['title' => 'Project Manager', 'code' => 'POS-PM', 'level' => 'manager'],
            ['title' => 'Business Analyst', 'code' => 'POS-BA', 'level' => 'mid'],
            ['title' => 'Account Manager', 'code' => 'POS-AM', 'level' => 'senior'],
            ['title' => 'Data Engineer', 'code' => 'POS-DE', 'level' => 'mid'],
            ['title' => 'Security Specialist', 'code' => 'POS-SEC', 'level' => 'senior'],
            ['title' => 'Customer Service Agent', 'code' => 'POS-CSA', 'level' => 'junior'],
            ['title' => 'Finance Analyst', 'code' => 'POS-FA', 'level' => 'junior'],
            ['title' => 'UX Designer', 'code' => 'POS-UX', 'level' => 'mid'],
            ['title' => 'DevOps Engineer', 'code' => 'POS-DEVOPS', 'level' => 'senior'],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['code' => $position['code']], $position);
        }

        $locations = [
            ['name' => 'Algiers HQ', 'city' => 'Algiers', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Oran Branch', 'city' => 'Oran', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Constantine Branch', 'city' => 'Constantine', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Annaba Branch', 'city' => 'Annaba', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Tlemcen Branch', 'city' => 'Tlemcen', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['name' => $location['name']], $location);
        }
    }
}
