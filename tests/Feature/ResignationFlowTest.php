<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Resignation;

class ResignationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_resignation_status_flow()
    {
        // Create users manually
        $company = User::create([
            'name' => 'Company User',
            'email' => 'company@test.com',
            'password' => bcrypt('password'),
            'type' => 'company',
            'created_by' => 1
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com', 
            'password' => bcrypt('password'),
            'type' => 'employee',
            'created_by' => 1
        ]);

        $hrUser = User::create([
            'name' => 'HR User',
            'email' => 'hr@test.com',
            'password' => bcrypt('password'), 
            'type' => 'employee',
            'created_by' => 1
        ]);

        // Create employees manually
        $managerEmployee = Employee::create([
            'user_id' => $manager->id,
            'name' => 'Manager Employee',
            'created_by' => 1,
            'department_id' => 1,
            'designation_id' => 1
        ]);

        $hrEmployee = Employee::create([
            'user_id' => $hrUser->id,
            'name' => 'HR Employee', 
            'created_by' => 1,
            'department_id' => 2,
            'designation_id' => 2
        ]);

        $regularEmployee = Employee::create([
            'user_id' => 999, // dummy user
            'name' => 'Regular Employee',
            'created_by' => 1,
            'department_id' => 1,
            'designation_id' => 3
        ]);

        // Create resignation
        $resignation = Resignation::create([
            'employee_id' => $regularEmployee->id,
            'notice_date' => '2026-02-01',
            'resignation_date' => '2026-02-15',
            'description' => 'Test resignation',
            'created_by' => 1,
            'status' => 'pending'
        ]);

        // Test initial status
        $this->assertEquals('pending', $resignation->status);

        // Test that status fields exist
        $this->assertNotNull($resignation->status);
        $this->assertNull($resignation->approved_by);
        $this->assertNull($resignation->approved_at);

        // Test status transitions
        $resignation->update([
            'status' => 'manager_approved',
            'approved_by' => $manager->id,
            'approved_at' => now()
        ]);

        $resignation->refresh();
        $this->assertEquals('manager_approved', $resignation->status);
        $this->assertEquals($manager->id, $resignation->approved_by);

        $resignation->update([
            'status' => 'approved'
        ]);

        $resignation->refresh();
        $this->assertEquals('approved', $resignation->status);
    }
}
