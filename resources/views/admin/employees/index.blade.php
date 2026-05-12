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
        background-color: #ef4444;
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    .delete-button:hover {
        background-color: #b91c1c;
    }

    /* New button style */
    .add-employee-button {
        background-color: #0f6b4f;
        color: white;
        padding: 6px 12px;       /* reduced from 12px 20px */
        border-radius: 8px;
        text-decoration: none;
        font-weight: 300;
        float: right;
        margin-bottom: 10px;
        transition: background-color 0.3s ease;
        font-size: 25px;         /* add smaller font size */
    }
    .add-employee-button:hover {
        background-color: #173023ff;
    }
    h1 {
        font-weight: 700;
        font-size: 2.4rem;
        color: #2c3e50;
        margin: 0 0 20px 0;
        border-left: 6px solid #138056ff;
        padding-left: 15px;
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
