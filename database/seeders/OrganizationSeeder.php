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
            ['name' => 'Consumer Mobile', 'code' => 'BU-CONSUMER', 'description' => 'Retail, distribution, brand and customer experience for the mass market.'],
            ['name' => 'Enterprise & B2B', 'code' => 'BU-ENTERPRISE', 'description' => 'Corporate, government and wholesale connectivity and solutions.'],
            ['name' => 'Network & Infrastructure', 'code' => 'BU-NETWORK', 'description' => 'Mobile core, radio access, IP/transport and enterprise IT services.'],
            ['name' => 'Digital & Enablement', 'code' => 'BU-DIGITAL', 'description' => 'Digital products, data, architecture, QA and innovation.'],
            ['name' => 'Corporate Services', 'code' => 'BU-CORPORATE', 'description' => 'Finance, HR, procurement, legal, communications and strategy.'],
        ];

        foreach ($businessUnits as $unit) {
            BusinessUnit::firstOrCreate(['code' => $unit['code']], $unit);
        }

        $businessUnitId = fn (string $code): int => BusinessUnit::where('code', $code)->value('id');
        $departments = [
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Retail Operations', 'code' => 'DEPT-RETAIL'],
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Distribution & Trade Marketing', 'code' => 'DEPT-DISTRIBUTION'],
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Marketing & Brand', 'code' => 'DEPT-MARKETING'],
            ['business_unit_code' => 'BU-CONSUMER', 'name' => 'Customer Experience', 'code' => 'DEPT-CARE'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'Enterprise Sales', 'code' => 'DEPT-ENT-SALES'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'Key Accounts & Wholesale', 'code' => 'DEPT-KEY-ACC'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'Solutions Engineering', 'code' => 'DEPT-SOLUTIONS'],
            ['business_unit_code' => 'BU-ENTERPRISE', 'name' => 'IoT & Fixed Connectivity', 'code' => 'DEPT-IOT'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'Core Network & IP', 'code' => 'DEPT-CORE-NET'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'Radio Access & Field Operations', 'code' => 'DEPT-RAN'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'Network Security', 'code' => 'DEPT-NET-SEC'],
            ['business_unit_code' => 'BU-NETWORK', 'name' => 'IT & Digital Services', 'code' => 'DEPT-IT'],
            ['business_unit_code' => 'BU-DIGITAL', 'name' => 'Digital Products & Channels', 'code' => 'DEPT-DIGITAL'],
            ['business_unit_code' => 'BU-DIGITAL', 'name' => 'Data & Analytics Office', 'code' => 'DEPT-DATA'],
            ['business_unit_code' => 'BU-DIGITAL', 'name' => 'Architecture & Innovation', 'code' => 'DEPT-ARCH'],
            ['business_unit_code' => 'BU-DIGITAL', 'name' => 'Quality Assurance & Testing', 'code' => 'DEPT-QA'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Finance', 'code' => 'DEPT-FINANCE'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Human Resources', 'code' => 'DEPT-HR'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Procurement & Supply Chain', 'code' => 'DEPT-PROC'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Legal & Compliance', 'code' => 'DEPT-LEGAL'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Corporate Communications', 'code' => 'DEPT-COMMS'],
            ['business_unit_code' => 'BU-CORPORATE', 'name' => 'Strategy & Planning', 'code' => 'DEPT-STRATEGY'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], [
                'business_unit_id' => $businessUnitId($department['business_unit_code']),
                'name' => $department['name'],
            ]);
        }

        $departmentId = fn (string $code): int => Department::where('code', $code)->value('id');

        $teams = [
            ['department_code' => 'DEPT-RETAIL', 'name' => 'Store Operations North', 'code' => 'TEAM-STORE-NORTH'],
            ['department_code' => 'DEPT-RETAIL', 'name' => 'Store Operations West', 'code' => 'TEAM-STORE-WEST'],
            ['department_code' => 'DEPT-RETAIL', 'name' => 'Store Operations East', 'code' => 'TEAM-STORE-EAST'],
            ['department_code' => 'DEPT-RETAIL', 'name' => 'Flagship Stores Team', 'code' => 'TEAM-FLAGSHIP'],
            ['department_code' => 'DEPT-DISTRIBUTION', 'name' => 'Dealer Network Team', 'code' => 'TEAM-DEALERS'],
            ['department_code' => 'DEPT-DISTRIBUTION', 'name' => 'Wholesale & Logistics Team', 'code' => 'TEAM-WHOLESALE'],
            ['department_code' => 'DEPT-MARKETING', 'name' => 'Brand & Campaigns Team', 'code' => 'TEAM-BRAND'],
            ['department_code' => 'DEPT-MARKETING', 'name' => 'Digital Marketing Team', 'code' => 'TEAM-DIGITAL-MKT'],
            ['department_code' => 'DEPT-MARKETING', 'name' => 'Market Research Team', 'code' => 'TEAM-MKT-RESEARCH'],
            ['department_code' => 'DEPT-CARE', 'name' => 'Call Center Team', 'code' => 'TEAM-CALL-CENTER'],
            ['department_code' => 'DEPT-CARE', 'name' => 'Chat & Social Team', 'code' => 'TEAM-CHAT-SOCIAL'],
            ['department_code' => 'DEPT-CARE', 'name' => 'Field Quality Team', 'code' => 'TEAM-FIELD-QUALITY'],
            ['department_code' => 'DEPT-CARE', 'name' => 'Complaints Resolution Team', 'code' => 'TEAM-COMPLAINTS'],
            ['department_code' => 'DEPT-ENT-SALES', 'name' => 'Government & Public Sector Team', 'code' => 'TEAM-GOV-SALES'],
            ['department_code' => 'DEPT-ENT-SALES', 'name' => 'Corporate Accounts Team', 'code' => 'TEAM-CORP-SALES'],
            ['department_code' => 'DEPT-ENT-SALES', 'name' => 'SME Team', 'code' => 'TEAM-SME-SALES'],
            ['department_code' => 'DEPT-KEY-ACC', 'name' => 'Key Accounts Team', 'code' => 'TEAM-KEY-ACCOUNTS'],
            ['department_code' => 'DEPT-KEY-ACC', 'name' => 'Wholesale & Roaming Team', 'code' => 'TEAM-WHOLESALE-ROAM'],
            ['department_code' => 'DEPT-SOLUTIONS', 'name' => 'Pre-Sales Team', 'code' => 'TEAM-PRE-SALES'],
            ['department_code' => 'DEPT-SOLUTIONS', 'name' => 'Post-Sales Solutions Team', 'code' => 'TEAM-POST-SALES'],
            ['department_code' => 'DEPT-SOLUTIONS', 'name' => 'Managed Services Team', 'code' => 'TEAM-MANAGED-SVC'],
            ['department_code' => 'DEPT-IOT', 'name' => 'IoT Platform Team', 'code' => 'TEAM-IOT'],
            ['department_code' => 'DEPT-IOT', 'name' => 'Fixed Broadband Team', 'code' => 'TEAM-FIXED-BB'],
            ['department_code' => 'DEPT-CORE-NET', 'name' => 'Packet Core Team', 'code' => 'TEAM-PACKET-CORE'],
            ['department_code' => 'DEPT-CORE-NET', 'name' => 'IP / MPLS Backbone Team', 'code' => 'TEAM-IP-MPLS'],
            ['department_code' => 'DEPT-CORE-NET', 'name' => 'Transmission & DWDM Team', 'code' => 'TEAM-TRANSMISSION'],
            ['department_code' => 'DEPT-CORE-NET', 'name' => 'Voice Core & VAS Team', 'code' => 'TEAM-VOICE-VAS'],
            ['department_code' => 'DEPT-RAN', 'name' => 'Radio Planning Team', 'code' => 'TEAM-RADIO-PLAN'],
            ['department_code' => 'DEPT-RAN', 'name' => 'Radio Access Engineering Team', 'code' => 'TEAM-RAN-ENG'],
            ['department_code' => 'DEPT-RAN', 'name' => 'Field Operations North', 'code' => 'TEAM-FIELD-NORTH'],
            ['department_code' => 'DEPT-RAN', 'name' => 'Field Operations South', 'code' => 'TEAM-FIELD-SOUTH'],
            ['department_code' => 'DEPT-RAN', 'name' => 'Site & Tower Engineering Team', 'code' => 'TEAM-SITE-TOWER'],
            ['department_code' => 'DEPT-NET-SEC', 'name' => 'Security Operations Center', 'code' => 'TEAM-SOC'],
            ['department_code' => 'DEPT-NET-SEC', 'name' => 'Security Engineering Team', 'code' => 'TEAM-SEC-ENG'],
            ['department_code' => 'DEPT-IT', 'name' => 'Platform Engineering Team', 'code' => 'TEAM-PLATFORM'],
            ['department_code' => 'DEPT-IT', 'name' => 'Enterprise Applications Team', 'code' => 'TEAM-ENTERPRISE-APPS'],
            ['department_code' => 'DEPT-IT', 'name' => 'IT Service Desk Team', 'code' => 'TEAM-SERVICE-DESK'],
            ['department_code' => 'DEPT-IT', 'name' => 'Data Center Operations Team', 'code' => 'TEAM-DC-OPS'],
            ['department_code' => 'DEPT-DIGITAL', 'name' => 'Selfcare App Team', 'code' => 'TEAM-SELFCARE'],
            ['department_code' => 'DEPT-DIGITAL', 'name' => 'Web & E-Commerce Team', 'code' => 'TEAM-WEB-ECOMM'],
            ['department_code' => 'DEPT-DIGITAL', 'name' => 'Mobile Payments Team', 'code' => 'TEAM-MPAY'],
            ['department_code' => 'DEPT-DATA', 'name' => 'Data Platform Team', 'code' => 'TEAM-DATA-PLATFORM'],
            ['department_code' => 'DEPT-DATA', 'name' => 'BI & Reporting Team', 'code' => 'TEAM-BI'],
            ['department_code' => 'DEPT-DATA', 'name' => 'Data Science & AI Team', 'code' => 'TEAM-DS-AI'],
            ['department_code' => 'DEPT-ARCH', 'name' => 'Enterprise Architecture Team', 'code' => 'TEAM-EA'],
            ['department_code' => 'DEPT-ARCH', 'name' => 'R&D Innovation Team', 'code' => 'TEAM-RND'],
            ['department_code' => 'DEPT-QA', 'name' => 'Manual QA Team', 'code' => 'TEAM-MANUAL-QA'],
            ['department_code' => 'DEPT-QA', 'name' => 'Test Automation Team', 'code' => 'TEAM-AUTOMATION'],
            ['department_code' => 'DEPT-FINANCE', 'name' => 'Financial Planning & Analysis Team', 'code' => 'TEAM-FP'],
            ['department_code' => 'DEPT-FINANCE', 'name' => 'Accounting & Tax Team', 'code' => 'TEAM-ACCOUNTING'],
            ['department_code' => 'DEPT-FINANCE', 'name' => 'Treasury Team', 'code' => 'TEAM-TREASURY'],
            ['department_code' => 'DEPT-HR', 'name' => 'HR Operations Team', 'code' => 'TEAM-HR-OPS'],
            ['department_code' => 'DEPT-HR', 'name' => 'Talent Acquisition Team', 'code' => 'TEAM-TALENT'],
            ['department_code' => 'DEPT-HR', 'name' => 'Learning & Development Team', 'code' => 'TEAM-LND'],
            ['department_code' => 'DEPT-PROC', 'name' => 'Strategic Sourcing Team', 'code' => 'TEAM-SOURCING'],
            ['department_code' => 'DEPT-PROC', 'name' => 'Logistics & Warehousing Team', 'code' => 'TEAM-LOGISTICS'],
            ['department_code' => 'DEPT-LEGAL', 'name' => 'Contracts Team', 'code' => 'TEAM-CONTRACTS'],
            ['department_code' => 'DEPT-LEGAL', 'name' => 'Compliance & Regulatory Team', 'code' => 'TEAM-COMPLIANCE'],
            ['department_code' => 'DEPT-COMMS', 'name' => 'Internal Communications Team', 'code' => 'TEAM-INTERNAL-COMMS'],
            ['department_code' => 'DEPT-COMMS', 'name' => 'External Relations Team', 'code' => 'TEAM-EXTERNAL-COMMS'],
            ['department_code' => 'DEPT-STRATEGY', 'name' => 'Market Strategy Team', 'code' => 'TEAM-MKT-STRATEGY'],
            ['department_code' => 'DEPT-STRATEGY', 'name' => 'Project Management Office', 'code' => 'TEAM-PMO'],
        ];

        foreach ($teams as $team) {
            Team::firstOrCreate(['code' => $team['code']], [
                'department_id' => $departmentId($team['department_code']),
                'name' => $team['name'],
            ]);
        }

        $positions = [
            ['title' => 'Business Unit Director', 'code' => 'POS-DIR', 'level' => 'director'],
            ['title' => 'Department Manager', 'code' => 'POS-DEPT-MGR', 'level' => 'manager'],
            ['title' => 'Team Leader', 'code' => 'POS-TL', 'level' => 'manager'],
            ['title' => 'Enterprise Architect', 'code' => 'POS-EA', 'level' => 'director'],
            ['title' => 'Network Architect', 'code' => 'POS-NET-ARCH', 'level' => 'director'],
            ['title' => 'Cloud Architect', 'code' => 'POS-CLOUD-ARCH', 'level' => 'director'],
            ['title' => 'Project Manager', 'code' => 'POS-PM', 'level' => 'manager'],
            ['title' => 'Product Manager', 'code' => 'POS-PDM', 'level' => 'manager'],
            ['title' => 'Financial Controller', 'code' => 'POS-FC', 'level' => 'manager'],
            ['title' => 'Store Manager', 'code' => 'POS-STM', 'level' => 'manager'],
            ['title' => 'Brand Manager', 'code' => 'POS-BRAND', 'level' => 'manager'],
            ['title' => 'Contact Center Supervisor', 'code' => 'POS-CC-SUP', 'level' => 'manager'],
            ['title' => 'Software Engineer', 'code' => 'POS-SWE', 'level' => 'mid'],
            ['title' => 'Senior Software Engineer', 'code' => 'POS-SR-SWE', 'level' => 'senior'],
            ['title' => 'Lead Software Engineer', 'code' => 'POS-LEAD-SWE', 'level' => 'senior'],
            ['title' => 'Network Engineer', 'code' => 'POS-NET-ENG', 'level' => 'mid'],
            ['title' => 'Senior Network Engineer', 'code' => 'POS-SR-NET', 'level' => 'senior'],
            ['title' => 'Radio Engineer', 'code' => 'POS-RAD-ENG', 'level' => 'mid'],
            ['title' => 'Senior Radio Engineer', 'code' => 'POS-SR-RAD', 'level' => 'senior'],
            ['title' => 'Field Engineer', 'code' => 'POS-FIELD-ENG', 'level' => 'mid'],
            ['title' => 'Security Engineer', 'code' => 'POS-SEC-ENG', 'level' => 'mid'],
            ['title' => 'SOC Analyst', 'code' => 'POS-SOC', 'level' => 'junior'],
            ['title' => 'Data Engineer', 'code' => 'POS-DE', 'level' => 'mid'],
            ['title' => 'Data Analyst', 'code' => 'POS-DA', 'level' => 'junior'],
            ['title' => 'Data Scientist', 'code' => 'POS-DS', 'level' => 'senior'],
            ['title' => 'DevOps Engineer', 'code' => 'POS-DEVOPS', 'level' => 'senior'],
            ['title' => 'Database Administrator', 'code' => 'POS-DBA', 'level' => 'mid'],
            ['title' => 'QA Tester', 'code' => 'POS-QA', 'level' => 'junior'],
            ['title' => 'Test Automation Engineer', 'code' => 'POS-QA-ENG', 'level' => 'mid'],
            ['title' => 'UX Designer', 'code' => 'POS-UX', 'level' => 'mid'],
            ['title' => 'Business Analyst', 'code' => 'POS-BA', 'level' => 'mid'],
            ['title' => 'Customer Service Agent', 'code' => 'POS-CSA', 'level' => 'junior'],
            ['title' => 'Senior Customer Service Agent', 'code' => 'POS-SR-CSA', 'level' => 'mid'],
            ['title' => 'Account Manager', 'code' => 'POS-AM', 'level' => 'senior'],
            ['title' => 'Key Account Manager', 'code' => 'POS-KAM', 'level' => 'senior'],
            ['title' => 'Sales Representative', 'code' => 'POS-SALES', 'level' => 'junior'],
            ['title' => 'Retail Associate', 'code' => 'POS-RA', 'level' => 'junior'],
            ['title' => 'Marketing Specialist', 'code' => 'POS-MKT', 'level' => 'mid'],
            ['title' => 'Market Research Analyst', 'code' => 'POS-MRA', 'level' => 'junior'],
            ['title' => 'Finance Analyst', 'code' => 'POS-FA', 'level' => 'junior'],
            ['title' => 'Senior Finance Analyst', 'code' => 'POS-SR-FA', 'level' => 'mid'],
            ['title' => 'Accountant', 'code' => 'POS-ACC', 'level' => 'junior'],
            ['title' => 'HR Business Partner', 'code' => 'POS-HRBP', 'level' => 'mid'],
            ['title' => 'Talent Acquisition Specialist', 'code' => 'POS-TA', 'level' => 'mid'],
            ['title' => 'HR Assistant', 'code' => 'POS-HRA', 'level' => 'junior'],
            ['title' => 'Procurement Specialist', 'code' => 'POS-PROC', 'level' => 'mid'],
            ['title' => 'Supply Chain Analyst', 'code' => 'POS-SCA', 'level' => 'junior'],
            ['title' => 'Legal Counsel', 'code' => 'POS-LGL', 'level' => 'senior'],
            ['title' => 'Compliance Officer', 'code' => 'POS-COC', 'level' => 'mid'],
            ['title' => 'Communication Officer', 'code' => 'POS-COO', 'level' => 'mid'],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['code' => $position['code']], $position);
        }

        $locations = [
            ['name' => 'Algiers Headquarters', 'city' => 'Algiers', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Oran Regional Office', 'city' => 'Oran', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Constantine Regional Office', 'city' => 'Constantine', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Annaba Branch', 'city' => 'Annaba', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Tlemcen Branch', 'city' => 'Tlemcen', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Sétif Branch', 'city' => 'Sétif', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Blida Branch', 'city' => 'Blida', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Skikda Branch', 'city' => 'Skikda', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Béjaïa Branch', 'city' => 'Béjaïa', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Ouargla Network Ops Site', 'city' => 'Ouargla', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Ghardaïa Network Ops Site', 'city' => 'Ghardaïa', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['name' => 'Tamanrasset Network Ops Site', 'city' => 'Tamanrasset', 'country' => 'Algeria', 'timezone' => 'Africa/Algiers'],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['name' => $location['name']], $location);
        }
    }
}
