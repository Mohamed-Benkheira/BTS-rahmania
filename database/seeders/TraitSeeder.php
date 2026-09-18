<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Language;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TraitSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Programming',
            'Networking',
            'Database',
            'Security',
            'Cloud & DevOps',
            'Data & Analytics',
            'Business & Leadership',
            'Marketing & Sales',
            'Telecommunications',
        ];

        foreach ($categories as $name) {
            SkillCategory::firstOrCreate(['name' => $name]);
        }

        $skills = [
            ['name' => 'PHP', 'category' => 'Programming'],
            ['name' => 'Laravel', 'category' => 'Programming'],
            ['name' => 'Python', 'category' => 'Programming'],
            ['name' => 'Java', 'category' => 'Programming'],
            ['name' => 'JavaScript', 'category' => 'Programming'],
            ['name' => 'TypeScript', 'category' => 'Programming'],
            ['name' => 'React', 'category' => 'Programming'],
            ['name' => 'Vue.js', 'category' => 'Programming'],
            ['name' => 'Node.js', 'category' => 'Programming'],
            ['name' => 'C#', 'category' => 'Programming'],
            ['name' => 'Go', 'category' => 'Programming'],
            ['name' => 'SQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'Oracle DB', 'category' => 'Database'],
            ['name' => 'Data Modeling', 'category' => 'Database'],
            ['name' => 'ETL', 'category' => 'Database'],
            ['name' => 'TCP/IP', 'category' => 'Networking'],
            ['name' => 'Routing & Switching', 'category' => 'Networking'],
            ['name' => 'Firewalls', 'category' => 'Networking'],
            ['name' => 'MPLS', 'category' => 'Networking'],
            ['name' => '4G/5G Core', 'category' => 'Networking'],
            ['name' => 'Radio Access Networks', 'category' => 'Networking'],
            ['name' => 'Cisco IOS', 'category' => 'Networking'],
            ['name' => 'VoIP', 'category' => 'Networking'],
            ['name' => 'SDN/NFV', 'category' => 'Networking'],
            ['name' => 'Optical Transport (DWDM)', 'category' => 'Networking'],
            ['name' => 'Network Monitoring (NOC)', 'category' => 'Networking'],
            ['name' => 'SS7/Diameter', 'category' => 'Networking'],
            ['name' => 'Fiber Optics', 'category' => 'Networking'],
            ['name' => 'Information Security', 'category' => 'Security'],
            ['name' => 'Penetration Testing', 'category' => 'Security'],
            ['name' => 'SOC Analysis', 'category' => 'Security'],
            ['name' => 'IAM', 'category' => 'Security'],
            ['name' => 'Incident Response', 'category' => 'Security'],
            ['name' => 'Security Auditing', 'category' => 'Security'],
            ['name' => 'Cloud Architecture', 'category' => 'Cloud & DevOps'],
            ['name' => 'Docker', 'category' => 'Cloud & DevOps'],
            ['name' => 'Kubernetes', 'category' => 'Cloud & DevOps'],
            ['name' => 'CI/CD', 'category' => 'Cloud & DevOps'],
            ['name' => 'Linux Administration', 'category' => 'Cloud & DevOps'],
            ['name' => 'Terraform', 'category' => 'Cloud & DevOps'],
            ['name' => 'Site Reliability Engineering', 'category' => 'Cloud & DevOps'],
            ['name' => 'Agile Methodologies', 'category' => 'Business & Leadership'],
            ['name' => 'Project Management', 'category' => 'Business & Leadership'],
            ['name' => 'Stakeholder Management', 'category' => 'Business & Leadership'],
            ['name' => 'Financial Analysis', 'category' => 'Business & Leadership'],
            ['name' => 'Requirements Analysis', 'category' => 'Business & Leadership'],
            ['name' => 'Business Process Modeling', 'category' => 'Business & Leadership'],
            ['name' => 'Negotiation', 'category' => 'Business & Leadership'],
            ['name' => 'Customer Relationship Management', 'category' => 'Business & Leadership'],
            ['name' => 'UI/UX Design', 'category' => 'Business & Leadership'],
            ['name' => 'Technical Writing', 'category' => 'Business & Leadership'],
            ['name' => 'Change Management', 'category' => 'Business & Leadership'],
            ['name' => 'Budgeting', 'category' => 'Business & Leadership'],
            ['name' => 'Risk Management', 'category' => 'Business & Leadership'],
            ['name' => 'Coaching & Mentoring', 'category' => 'Business & Leadership'],
            ['name' => 'Workforce Planning', 'category' => 'Business & Leadership'],
            ['name' => 'Digital Marketing', 'category' => 'Marketing & Sales'],
            ['name' => 'Content Creation', 'category' => 'Marketing & Sales'],
            ['name' => 'SEO/SEM', 'category' => 'Marketing & Sales'],
            ['name' => 'Brand Strategy', 'category' => 'Marketing & Sales'],
            ['name' => 'Retail Operations', 'category' => 'Marketing & Sales'],
            ['name' => 'B2B Sales', 'category' => 'Marketing & Sales'],
            ['name' => 'Public Relations', 'category' => 'Marketing & Sales'],
            ['name' => 'Campaign Management', 'category' => 'Marketing & Sales'],
            ['name' => 'Customer Experience Management', 'category' => 'Marketing & Sales'],
            ['name' => 'Market Research', 'category' => 'Marketing & Sales'],
            ['name' => 'Billing & Charging Systems', 'category' => 'Telecommunications'],
            ['name' => 'Prepaid Platforms', 'category' => 'Telecommunications'],
            ['name' => 'Value Added Services (VAS)', 'category' => 'Telecommunications'],
            ['name' => 'Mobile Payment', 'category' => 'Telecommunications'],
            ['name' => 'Roaming Operations', 'category' => 'Telecommunications'],
            ['name' => 'RF Planning', 'category' => 'Telecommunications'],
            ['name' => 'Antenna Systems', 'category' => 'Telecommunications'],
            ['name' => 'BSS/OSS', 'category' => 'Telecommunications'],
            ['name' => 'SIM Lifecycle Management', 'category' => 'Telecommunications'],
            ['name' => 'Call Center Technology (CTI)', 'category' => 'Telecommunications'],
            ['name' => 'Business Intelligence', 'category' => 'Data & Analytics'],
            ['name' => 'Power BI', 'category' => 'Data & Analytics'],
            ['name' => 'Tableau', 'category' => 'Data & Analytics'],
            ['name' => 'Machine Learning', 'category' => 'Data & Analytics'],
            ['name' => 'Statistics', 'category' => 'Data & Analytics'],
            ['name' => 'Big Data (Hadoop)', 'category' => 'Data & Analytics'],
            ['name' => 'Excel', 'category' => 'Data & Analytics'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['slug' => Str::slug($skill['name'])], [
                'name' => $skill['name'],
                'slug' => Str::slug($skill['name']),
                'skill_category_id' => SkillCategory::where('name', $skill['category'])->value('id'),
            ]);
        }

        $certifications = [
            ['name' => 'Cisco CCNA', 'issuer' => 'Cisco', 'validity_period_months' => 36],
            ['name' => 'Cisco CCNP', 'issuer' => 'Cisco', 'validity_period_months' => 36],
            ['name' => 'Cisco CCIE Enterprise', 'issuer' => 'Cisco', 'validity_period_months' => 36],
            ['name' => 'AWS Solutions Architect', 'issuer' => 'Amazon Web Services', 'validity_period_months' => 36],
            ['name' => 'Azure Administrator', 'issuer' => 'Microsoft', 'validity_period_months' => 24],
            ['name' => 'Microsoft Azure Security Engineer (AZ-500)', 'issuer' => 'Microsoft', 'validity_period_months' => 24],
            ['name' => 'Google Cloud Associate Cloud Engineer', 'issuer' => 'Google', 'validity_period_months' => 24],
            ['name' => 'Kubernetes Administrator (CKA)', 'issuer' => 'CNCF', 'validity_period_months' => 36],
            ['name' => 'Certified Information Systems Security Professional', 'issuer' => 'ISC2', 'validity_period_months' => 36],
            ['name' => 'CISSP', 'issuer' => 'ISC2', 'validity_period_months' => 36],
            ['name' => 'CISA', 'issuer' => 'ISACA', 'validity_period_months' => 36],
            ['name' => 'CISM', 'issuer' => 'ISACA', 'validity_period_months' => 36],
            ['name' => 'Certified Scrum Master', 'issuer' => 'Scrum Alliance', 'validity_period_months' => 24],
            ['name' => 'PMP', 'issuer' => 'PMI', 'validity_period_months' => 36],
            ['name' => 'PRINCE2 Foundation', 'issuer' => 'AXELOS', 'validity_period_months' => 36],
            ['name' => 'ITIL 4 Foundation', 'issuer' => 'AXELOS', 'validity_period_months' => 36],
            ['name' => 'Oracle Certified Professional', 'issuer' => 'Oracle', 'validity_period_months' => null],
            ['name' => 'Red Hat Certified Engineer', 'issuer' => 'Red Hat', 'validity_period_months' => 36],
            ['name' => 'Fortinet NSE 4', 'issuer' => 'Fortinet', 'validity_period_months' => 24],
            ['name' => 'Check Point CCSA', 'issuer' => 'Check Point', 'validity_period_months' => 24],
            ['name' => 'CompTIA Security+', 'issuer' => 'CompTIA', 'validity_period_months' => 36],
            ['name' => 'Huawei HCIA', 'issuer' => 'Huawei', 'validity_period_months' => 36],
            ['name' => 'Nokia NRS I', 'issuer' => 'Nokia', 'validity_period_months' => 24],
            ['name' => 'Ericsson Certified LTE Fundamentals', 'issuer' => 'Ericsson', 'validity_period_months' => 48],
            ['name' => 'Salesforce Administrator', 'issuer' => 'Salesforce', 'validity_period_months' => 24],
            ['name' => 'Power BI Data Analyst (PL-300)', 'issuer' => 'Microsoft', 'validity_period_months' => 24],
            ['name' => 'Tableau Desktop Specialist', 'issuer' => 'Tableau', 'validity_period_months' => 24],
            ['name' => 'Google Analytics Certification', 'issuer' => 'Google', 'validity_period_months' => 12],
            ['name' => 'Lean Six Sigma Yellow Belt', 'issuer' => 'IASSC', 'validity_period_months' => null],
            ['name' => 'Google Cloud Professional', 'issuer' => 'Google', 'validity_period_months' => 24],
        ];

        foreach ($certifications as $certification) {
            Certification::firstOrCreate(['slug' => Str::slug($certification['name'])], $certification);
        }

        $languages = [
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Tamazight', 'code' => 'ber'],
            ['name' => 'Mandarin', 'code' => 'zh'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Turkish', 'code' => 'tr'],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(['code' => $language['code']], $language);
        }
    }
}
