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
            'Business & Leadership',
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
            ['name' => 'SQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'Data Modeling', 'category' => 'Database'],
            ['name' => 'TCP/IP', 'category' => 'Networking'],
            ['name' => 'Routing & Switching', 'category' => 'Networking'],
            ['name' => 'Firewalls', 'category' => 'Networking'],
            ['name' => 'MPLS', 'category' => 'Networking'],
            ['name' => '4G/5G Core', 'category' => 'Networking'],
            ['name' => 'Radio Access Networks', 'category' => 'Networking'],
            ['name' => 'Cisco IOS', 'category' => 'Networking'],
            ['name' => 'Information Security', 'category' => 'Security'],
            ['name' => 'Penetration Testing', 'category' => 'Security'],
            ['name' => 'SOC Analysis', 'category' => 'Security'],
            ['name' => 'IAM', 'category' => 'Security'],
            ['name' => 'Cloud Architecture', 'category' => 'Cloud & DevOps'],
            ['name' => 'Docker', 'category' => 'Cloud & DevOps'],
            ['name' => 'Kubernetes', 'category' => 'Cloud & DevOps'],
            ['name' => 'CI/CD', 'category' => 'Cloud & DevOps'],
            ['name' => 'Linux Administration', 'category' => 'Cloud & DevOps'],
            ['name' => 'Terraform', 'category' => 'Cloud & DevOps'],
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
            ['name' => 'AWS Solutions Architect', 'issuer' => 'Amazon Web Services', 'validity_period_months' => 36],
            ['name' => 'Azure Administrator', 'issuer' => 'Microsoft', 'validity_period_months' => 24],
            ['name' => 'Certified Information Systems Security Professional', 'issuer' => 'ISC2', 'validity_period_months' => 36],
            ['name' => 'Certified Scrum Master', 'issuer' => 'Scrum Alliance', 'validity_period_months' => 24],
            ['name' => 'PMP', 'issuer' => 'PMI', 'validity_period_months' => 36],
            ['name' => 'Oracle Certified Professional', 'issuer' => 'Oracle', 'validity_period_months' => null],
            ['name' => 'Google Cloud Professional', 'issuer' => 'Google', 'validity_period_months' => 24],
            ['name' => 'Red Hat Certified Engineer', 'issuer' => 'Red Hat', 'validity_period_months' => 36],
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
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(['code' => $language['code']], $language);
        }
    }
}
