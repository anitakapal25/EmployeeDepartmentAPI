<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_departments()
    {
        // Create some departments
        Department::factory()->count(3)->create();

        // Perform the request
        $response = $this->getJson('/api/departments');

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => ['id', 'name', 'created_at', 'updated_at']
                 ]);
    }

    /** @test */
    public function it_can_create_a_new_department()
    {
        $data = ['name' => 'New Department'];

        // Perform the request
        $response = $this->postJson('/api/departments', $data);

        // Assert the response
        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New Department']);
    }

    /** @test */
    public function it_cannot_create_a_department_without_a_name()
    {
        $data = ['name'=>''];

        // Perform the request
        $response = $this->postJson('/api/departments', $data,['Accept' => 'application/json']);
        // Assert the response
        $response->assertStatus(422)
                ->assertJsonFragment([
                    'name' => ['The name field is required.']
                ]);
    }

    /** @test */
    public function it_cannot_create_a_department_with_duplicate_name()
    {
        // Create an existing department
        Department::factory()->create(['name' => 'Existing Department']);

        $data = ['name' => 'Existing Department'];

        // Perform the request
        $response = $this->postJson('/api/departments', $data,['Accept' => 'application/json']);

        // Assert the response
        $response->assertStatus(422)
                ->assertJsonFragment([
                    'name' => ['The name has already been taken.']
                ]);
    }

    /** @test */
    public function it_can_show_a_department()
    {
        // Create a department
        $department = Department::factory()->create();

        // Perform the request
        $response = $this->getJson("/api/departments/{$department->id}");

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $department->name]);
    }

    /** @test */
    public function it_can_update_a_department()
    {
        // Create a department
        $department = Department::factory()->create(['name' => 'Old Name']);

        $data = ['name' => 'Updated Name'];

        // Perform the request
        $response = $this->putJson("/api/departments/{$department->id}", $data);

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function it_cannot_update_a_department_with_existing_name()
    {
        // Create two departments
        Department::factory()->create(['name' => 'First Department']);
        $departmentToUpdate = Department::factory()->create(['name' => 'Second Department']);

        $data = ['name' => 'First Department'];

        // Perform the request
        $response = $this->putJson("/api/departments/{$departmentToUpdate->id}", $data);

        // Assert the response
        $response->assertStatus(422)
                    ->assertJsonFragment([
                        'name' => ['The name has already been taken.']
                    ]);
    }

    /** @test */
    public function it_can_delete_a_department()
    {
        // Create a department
        $department = Department::factory()->create();

        // Perform the request
        $response = $this->deleteJson("/api/departments/{$department->id}");

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Department Deleted Successfully.']);

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
