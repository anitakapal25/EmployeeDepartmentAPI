<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Employee::with(['department', 'contacts', 'addresses'])->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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

        $employee = Employee::create($validated);
        if ($request->has('contacts')) {
            $employee->contacts()->createMany($request->contacts);
        }
        if ($request->has('addresses')) {
            $employee->addresses()->createMany($request->addresses);
        }

        return response()->json($employee->load(['contacts', 'addresses']), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($employee->load(['department', 'contacts', 'addresses']), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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

        $employee->update($validated);
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
        $employee->delete();

        return response()->json(null, 204);
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
