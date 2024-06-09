<?php

namespace App\Repositories;

use App\Models\Employee;

interface EmployeeRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update(Employee $employee, array $data);
    public function delete(Employee $employee);
    public function search($name);
}
