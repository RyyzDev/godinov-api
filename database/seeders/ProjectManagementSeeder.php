<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('project_timelines')->delete();
        DB::table('tasks')->delete();
        DB::table('project_progress')->delete();
        DB::table('project_team')->delete();
        DB::table('projects')->delete();
        DB::table('team_members')->delete();

        // Seed Team Members
        $teamMembers = [
            ['name' => 'Sarah', 'email' => 'sarah@company.com', 'role' => 'uiux'],
            ['name' => 'Budi', 'email' => 'budi@company.com', 'role' => 'backend'],
            ['name' => 'Joko', 'email' => 'joko@company.com', 'role' => 'backend'],
            ['name' => 'Andi', 'email' => 'andi@company.com', 'role' => 'frontend'],
        ];

        foreach ($teamMembers as $member) {
            DB::table('team_members')->insert([
                'name' => $member['name'],
                'email' => $member['email'],
                'role' => $member['role'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Project 1: E-Commerce Revamp 2024
        $project1Id = DB::table('projects')->insertGetId([
            'project_code' => 'ECOM-2024',
            'name' => 'E-Commerce Revamp 2024',
            'description' => 'Redesign total platform e-commerce dengan microservices architecture.',
            'client_name' => 'TechMart Indonesia',
            'service_type' => 'Full Stack Development',
            'deadline' => '2024-12-20',
            'status' => 'Completed',
            'team_count' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Progress for Project 1
        DB::table('project_progress')->insert([
            ['project_id' => $project1Id, 'role_type' => 'uiux', 'progress_percentage' => 100],
            ['project_id' => $project1Id, 'role_type' => 'backend', 'progress_percentage' => 100],
            ['project_id' => $project1Id, 'role_type' => 'frontend', 'progress_percentage' => 100],
        ]);

        // Tasks for Project 1
        $tasks1 = [
            ['title' => 'Wireframe Homepage', 'role' => 'uiux', 'status' => 'Done', 'priority' => 'High', 'assignee' => 'Sarah', 'order' => 1],
            ['title' => 'Design System Tokens', 'role' => 'uiux', 'status' => 'In Progress', 'priority' => 'Medium', 'assignee' => 'Sarah', 'order' => 2],
            ['title' => 'Setup Database Schema', 'role' => 'backend', 'status' => 'Done', 'priority' => 'High', 'assignee' => 'Budi', 'order' => 3],
            ['title' => 'API Authentication (Sanctum)', 'role' => 'backend', 'status' => 'In Progress', 'priority' => 'High', 'assignee' => 'Budi', 'order' => 4],
            ['title' => 'Product List API', 'role' => 'backend', 'status' => 'Todo', 'priority' => 'Medium', 'assignee' => 'Joko', 'order' => 5],
            ['title' => 'Setup React Router', 'role' => 'frontend', 'status' => 'Done', 'priority' => 'High', 'assignee' => 'Andi', 'order' => 6],
            ['title' => 'Component Library Setup', 'role' => 'frontend', 'status' => 'Todo', 'priority' => 'Low', 'assignee' => 'Andi', 'order' => 7],
        ];

        foreach ($tasks1 as $task) {
            DB::table('tasks')->insert([
                'project_id' => $project1Id,
                'title' => $task['title'],
                'role' => $task['role'],
                'status' => $task['status'],
                'priority' => $task['priority'],
                'assignee' => $task['assignee'],
                'order' => $task['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Project 2: Internal HR Dashboard
        $project2Id = DB::table('projects')->insertGetId([
            'project_code' => 'HR-DASH-2024',
            'name' => 'Internal HR Dashboard',
            'description' => 'Sistem manajemen karyawan dan payroll terintegrasi.',
            'client_name' => 'Internal Company',
            'service_type' => 'Enterprise System',
            'deadline' => '2024-11-15',
            'status' => 'Review',
            'team_count' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_progress')->insert([
            ['project_id' => $project2Id, 'role_type' => 'uiux', 'progress_percentage' => 100],
            ['project_id' => $project2Id, 'role_type' => 'backend', 'progress_percentage' => 85],
            ['project_id' => $project2Id, 'role_type' => 'frontend', 'progress_percentage' => 80],
        ]);

        // Project 3: Mobile App POS System
        $project3Id = DB::table('projects')->insertGetId([
            'project_code' => 'POS-MOBILE-2025',
            'name' => 'Mobile App POS System',
            'description' => 'Aplikasi kasir berbasis Android untuk klien retail.',
            'client_name' => 'RetailMax Chain',
            'service_type' => 'Mobile Application',
            'deadline' => '2025-01-10',
            'status' => 'Planning',
            'team_count' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_progress')->insert([
            ['project_id' => $project3Id, 'role_type' => 'uiux', 'progress_percentage' => 10],
            ['project_id' => $project3Id, 'role_type' => 'backend', 'progress_percentage' => 0],
            ['project_id' => $project3Id, 'role_type' => 'frontend', 'progress_percentage' => 0],
        ]);

        // Project 4: Cyber Security Web (Client Tracker Example)
        $project4Id = DB::table('projects')->insertGetId([
            'project_code' => 'GDN-CYBER',
            'name' => 'Guardian Cyber Security Suite',
            'description' => 'Enterprise-grade cyber security platform with real-time threat detection.',
            'client_name' => 'Arasaka Corp',
            'service_type' => 'Cyber Security Web',
            'deadline' => '2025-02-28',
            'status' => 'In Progress',
            'team_count' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_progress')->insert([
            ['project_id' => $project4Id, 'role_type' => 'uiux', 'progress_percentage' => 100],
            ['project_id' => $project4Id, 'role_type' => 'backend', 'progress_percentage' => 65],
            ['project_id' => $project4Id, 'role_type' => 'frontend', 'progress_percentage' => 50],
        ]);

        // Timeline for GDN-CYBER (Client Tracker)
        $timelines = [
            ['title' => 'Order Confirmed', 'date' => '10 Oct 2024', 'status' => 'completed', 'note' => 'Encryption keys generated. Contract signed.', 'order' => 1],
            ['title' => 'Blueprint Design', 'date' => '15 Oct 2024', 'status' => 'completed', 'note' => 'UI/UX wireframes approved by board.', 'order' => 2],
            ['title' => 'Core Development', 'date' => 'Sedang Diproses...', 'status' => 'current', 'note' => 'Injecting React components & API integration.', 'order' => 3],
            ['title' => 'System Audit', 'date' => 'Pending', 'status' => 'pending', 'note' => 'Vulnerability stress test.', 'order' => 4],
            ['title' => 'Deploy to Net', 'date' => 'Pending', 'status' => 'pending', 'note' => 'Final launch sequence.', 'order' => 5],
        ];

        foreach ($timelines as $timeline) {
            DB::table('project_timelines')->insert([
                'project_id' => $project4Id,
                'title' => $timeline['title'],
                'date' => $timeline['date'],
                'status' => $timeline['status'],
                'note' => $timeline['note'],
                'order' => $timeline['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✅ Database seeded successfully!\n";
        echo "Projects created: 4\n";
        echo "Tasks created: 7 (Project 1)\n";
        echo "Timeline entries: 5 (GDN-CYBER)\n";
    }
}