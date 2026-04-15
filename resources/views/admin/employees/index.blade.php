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

    /* Modal backdrop */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    /* Modal container */
    .modal {
        background: white;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        text-align: center;
        font-family: Arial, sans-serif;
    }

    .modal h2 {
        margin-top: 0;
        color: #e3342f;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .modal p {
        margin-bottom: 25px;
        font-size: 16px;
        color: #333;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .modal-button {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
    }

    .modal-button.cancel {
        background-color: #6b7280;
        color: white;
    }
    .modal-button.cancel:hover {
        background-color: #4b5563;
    }

    .modal-button.confirm {
        background-color: #ef4444;
        color: white;
    }
    .modal-button.confirm:hover {
        background-color: #b91c1c;
    }
</style>

<h1>Employees List
    <a href="{{ route('admin.employees.create') }}" class="add-employee-button">+ Add Employee</a>
</h1>

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
                    <button class="delete-button" onclick="openDeleteModal({{ $employee->id }})">Delete</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
        <h2 id="modalTitle">Confirm Deletion</h2>
        <p id="modalDesc">Are you sure you want to delete this employee? This action cannot be undone.</p>
        <div class="modal-buttons">
            <button class="modal-button cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="modal-button confirm" id="confirmDeleteButton">Delete</button>
        </div>
    </div>
</div>

<script>
    let employeeIdToDelete = null;

    function openDeleteModal(employeeId) {
        employeeIdToDelete = employeeId;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        employeeIdToDelete = null;
        document.getElementById('deleteModal').style.display = 'none';
    }

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        if (employeeIdToDelete) {
            const form = document.getElementById('delete-form-' + employeeIdToDelete);
            if (form) {
                form.submit();
            }
        }
    });

    // Close modal on backdrop click
    document.getElementById('deleteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDeleteModal();
        }
    });

    // Allow ESC key to close modal
    document.addEventListener('keydown', function(event) {
        if(event.key === "Escape" && document.getElementById('deleteModal').style.display === 'flex') {
            closeDeleteModal();
        }
    });
</script>
@endsection
