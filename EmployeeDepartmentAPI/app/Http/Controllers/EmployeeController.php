<?php

namespace App\Http\Controllers;

use App\Repositories\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Employee;
use Exception;

class EmployeeController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $employees = $this->employeeRepository->all();
            return response()->json($employees, 200);
        } catch (Exception $e) {
            \Log::error('Failed to retrieve employees: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
        //return response()->json(Employee::with(['department', 'contacts', 'addresses'])->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'department_id' => 'required|exists:departments,id',
                'contacts' => 'array',
                'contacts.*.phone_number' => 'required|max:20',
                'addresses' => 'array',
                'addresses.*.address_line1' => 'required|max:255',
                'addresses.*.city' => 'required|max:100',
                'addresses.*.state' => 'required|max:100',
                'addresses.*.zip' => 'required|max:20',
            ]);

            $employee = $this->employeeRepository->create($validated);
            return response()->json($employee, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            \Log::error('Failed to create employee: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $employee = $this->employeeRepository->find($id);
        
            return response()->json($employee, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Employee not found'], 404);
        } catch (Exception $e) {
            \Log::error('Failed to retrieve employee: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'department_id' => 'required|exists:departments,id',
            'contacts' => 'array',
            'contacts.*.phone_number' => 'required|max:20',
            'addresses' => 'array',
            'addresses.*.address_line1' => 'required|max:255',
            'addresses.*.city' => 'required|max:100',
            'addresses.*.state' => 'required|max:100',
            'addresses.*.zip' => 'required|max:20',
        ]);

        $employee->update($request->all());
        if ($request->has('contacts')) {
            $employee->contacts()->delete();
            $employee->contacts()->createMany($request->contacts);
        }
        if ($request->has('addresses')) {
            $employee->addresses()->delete();
            $employee->addresses()->createMany($request->addresses);
        }

        return response()->json($employee->load(['contacts', 'addresses']), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id); // Fetch the employee or throw a 404
            $this->employeeRepository->delete($employee);
            return response()->json(['message' => 'Employee Deleted Successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Employee not found'], 404);
        } catch (Exception $e) {
            \Log::error('Failed to delete employee: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500); 
        }
    }

    public function search($name)
    {
        $employees = Employee::with(['department', 'contacts', 'addresses'])
            ->where('first_name', 'like', "%{$name}%")
            ->orWhere('last_name', 'like', "%{$name}%")
            ->get();

        return response()->json($employees, 200);
    }
}
