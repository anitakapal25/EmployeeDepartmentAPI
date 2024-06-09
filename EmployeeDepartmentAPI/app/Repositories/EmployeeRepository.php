<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function all()
    {
        return Employee::with(['department', 'contacts', 'addresses'])->get();
    }

    public function find($id)
    {
        return Employee::with(['department', 'contacts', 'addresses'])->findOrFail($id);
    }

    public function create(array $data)
    {
        dd($data);
        $employee = Employee::create($data);

        if (isset($data['contacts'])) {
            $employee->contacts()->createMany($data['contacts']);
        }

        if (isset($data['addresses'])) {
            $employee->addresses()->createMany($data['addresses']);
        }

        return $employee->load(['contacts', 'addresses']);
    }

    public function update(Employee $employee, array $data)
    {
        $employee->update($data);

        if (isset($data['contacts'])) {
            $employee->contacts()->delete();
            $employee->contacts()->createMany($data['contacts']);
        }

        if (isset($data['addresses'])) {
            $employee->addresses()->delete();
            $employee->addresses()->createMany($data['addresses']);
        }

        return $employee->load(['contacts', 'addresses']);
    }

    public function delete(Employee $employee)
    {
        $employee->delete();
    }

    public function search($name)
    {
        return Employee::with(['department', 'contacts', 'addresses'])
            ->where('first_name', 'like', "%{$name}%")
            ->orWhere('last_name', 'like', "%{$name}%")
            ->get();
    }
}