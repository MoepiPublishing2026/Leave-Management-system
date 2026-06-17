@extends('layouts.admin')

@section('title', 'Employees List')

@section('content')
<style>
    /* Existing styles... */
    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
        font-family: Arial, sans-serif;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px 16px;
        text-align: left;
    }
    th {
        background-color: #dff8eeff;
        color: black;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .delete-button {
        background-color: #dc2626;
        border: 1px solid #b91c1c;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .delete-button:hover {
        background-color: #b91c1c;
        color: #fff;
    }
    .add-employee-button {
        background-color: #b2cd16;
        border: 1px solid #91bf14;
        color: #0a1347;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        float: right;
        margin-bottom: 10px;
        font-size: 16px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .add-employee-button:hover {
        background-color: #9eb812;
        color: #0a1347;
    }
    h1 {
        font-weight: 700;
        font-size: 2.4rem;
        color: #2c3e50;
        margin: 0 0 20px 0;
        letter-spacing: 0.04em;
        margin-bottom:30px;
    }

</style>

<div class="employees-header" style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom: 20px;">
    <h1 style="margin-bottom: 0;">Employees List</h1>
    <a href="{{ route('admin.employees.create') }}" class="add-employee-button">+ Add Employee</a>
</div>

<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $employee)
            <tr>
                <td>{{ $employee->first_name }}</td>
                <td>{{ $employee->last_name }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->department }}</td>
                <td>{{ $employee->designation }}</td>
                <td>{{ ucfirst($employee->role) }}</td>
                <td>{{ $employee->is_active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <form id="delete-form-{{ $employee->id }}" action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    <button type="button" class="delete-button delete-employee-btn" data-employee-id="{{ $employee->id }}">Delete</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    document.querySelectorAll('.delete-employee-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const employeeId = this.getAttribute('data-employee-id');
            const form = document.getElementById('delete-form-' + employeeId);
            if (!form) return;

            Swal.fire({
                icon: 'warning',
                title: 'Delete Employee?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
