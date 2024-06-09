<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_employees()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'first_name' => $employee->first_name,
                     'last_name' => $employee->last_name,
                     'department_id' => $employee->department_id,
                 ]);
    }

    /** @test */
    public function it_can_create_a_new_employee()
    {
        $department = Department::factory()->create();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $department->id,
            'contacts' => [
                ['phone_number' => '1234567890'],
            ],
            'addresses' => [
                [
                    'address_line1' => '123 Main St',
                    'city' => 'Townsville',
                    'state' => 'State',
                    'zip' => '12345',
                ],
            ],
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['first_name' => 'John']);
    }

    /** @test */
    public function it_can_show_an_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'first_name' => $employee->first_name,
                     'last_name' => $employee->last_name,
                 ]);
    }

    /** @test */
    public function it_can_update_an_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'department_id' => $department->id,
            'contacts' => [
                ['phone_number' => '0987654321'],
            ],
            'addresses' => [
                [
                    'address_line1' => '456 Elm St',
                    'city' => 'Cityville',
                    'state' => 'Province',
                    'zip' => '67890',
                ],
            ],
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['first_name' => 'Jane']);
    }

    /** @test */
    public function it_can_delete_an_employee()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    /** @test */
    public function it_can_search_employees_by_name()
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['first_name' => 'John', 'last_name' => 'Doe', 'department_id' => $department->id]);
        $anotherEmployee = Employee::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith', 'department_id' => $department->id]);

        $response = $this->getJson('/api/employees/search/John');

        $response->assertStatus(200)
                 ->assertJsonFragment(['first_name' => 'John'])
                 ->assertJsonMissing(['first_name' => 'Jane']);
    }
}
