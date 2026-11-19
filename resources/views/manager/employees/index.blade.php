@extends($layout ?? 'layouts.manager')

@section('title', 'Employees List')

@section('content')
<style>
    html body main:has(.employees-page) {
        display: block;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .employees-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px 28px;
        color: #1e293b;
        height: auto;
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    .employees-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        background: #fff;
        z-index: 5;
    }
    .employees-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #123f3a;
        margin: 0;
        letter-spacing: 0.04em;
    }
    
    .employees-table-wrap {
        position: relative;
        overflow: hidden;
        border: 2px solid #8fb3a8;
        border-radius: 0px;
        box-shadow:
            0 18px 45px rgba(15, 61, 46, 0.10),
            0 2px 8px rgba(15, 23, 42, 0.05);
        background: #d3e5df;
    }
    .employees-table-wrap::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        z-index: 3;
    }
    .employees-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.88rem;
        min-width: 0;
        table-layout: fixed;
    }
    .employees-table th,
    .employees-table td {
        padding: 12px 8px;
        text-align: left;
        border-bottom: 1px solid #A4B4F6;
        border-right: 1px solid #A4B4F6;
        vertical-align: middle;
        color: #000;
        background-color: #ffff;
    }
    .employees-table th:last-child,
    .employees-table td:last-child {
        border-right: none;
    }
    .employees-table thead {
        position: sticky;
        top: 0;
        z-index: 4;
    }
    .employees-table th {
        position: sticky;
        top: 0;
        z-index: 4;
        background-color: #2A6FA1;
        font-weight: 800;
        color: #fff;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: normal;
        border-bottom-color: #A4B4F6;
        border-right-color: #A4B4F6;
        box-shadow: 0 2px 0 #2A6FA1;
    }
    .employees-table th:first-child,
    .employees-table td:first-child {
        padding-left: 14px;
    }
    .employees-table th,
    .employees-table td {
        overflow: hidden;
        min-width: 0;
    }
    .employees-table td:first-child,
    .employees-table td:nth-child(2) {
        color: #000;
        font-weight: 700;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
        text-align: left;
    }
    .employees-table tbody tr {
        background: #d3e5df;
    }
    .employees-table tbody tr:hover {
        background: #bfd9d0;
    }
    .employees-table tbody tr:last-child td {
        border-bottom: 1px solid #A4B4F6;
    }
    .employees-email {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
        text-align: left;
        max-width: none;
    }
    .employees-dept,
    .employees-assigned {
        overflow-wrap: anywhere;
        word-break: break-word;
        max-width: none;
        line-height: 1.35;
        text-align: left;
    }
    .employee-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        min-width: 4.75rem;
        max-width: 100%;
        height: 28px;
        padding: 0 12px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        line-height: 1;
        color: #fff;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }
    .employee-status--active {
        background: #C6D300;
        color: #fff;
    }
    .employee-status--inactive {
        background: #64748b;
        color: #fff;
    }
    .employee-actions {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        align-items: center;
    }
    
    .edit-button,
    .delete-button {
        min-width: 84px;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        font-family: inherit;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .edit-button {
        background-color: #850621;
        border: 1px solid #680523;
        color: #fff;
    }
    .edit-button:hover {
        background-color: #680523;
        color: #fff;
    }
    .delete-button {
        background-color: #dc2626;
        border: 1px solid #b91c1c;
        color: #fff;
    }
    .delete-button:hover {
        background-color: #b91c1c;
        color: #fff;
    }
    .employees-empty {
        text-align: center;
        padding: 36px 20px;
        color: #475569;
        font-style: italic;
        font-weight: 600;
    }
    html body .employees-page .employees-table td .employee-actions a.edit-button,
html body .employees-page .employees-table td .employee-actions button.delete-button {
    background:#fff !important; background-color:#fff !important; background-image:none !important;
    border:1.5px solid #333 !important; color:#222 !important;
    border-radius:9px !important; width:34px !important; height:34px !important;
    min-width:0 !important; padding:0 !important; box-shadow:none !important;
    display:inline-flex !important; align-items:center !important; justify-content:center !important;
}
html body .employees-page .employees-table td .employee-actions a.edit-button:hover,
html body .employees-page .employees-table td .employee-actions button.delete-button:hover {
    background:#eef1f6 !important; color:#000 !important;
}
html body .employees-page .employees-table td .employee-actions svg { stroke:#222 !important; }

/* header text slightly larger like the prototype */
html body .employees-page .employees-table td,
html body .employees-page .employees-table th {
    overflow: hidden !important;
    min-width: 0 !important;
}
html body .employees-page .employees-table td:nth-child(7),
html body .employees-page .employees-table th:nth-child(7) {
    overflow: visible !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}
html body .employees-page .employees-table .employee-status {
    color: #fff !important;
}
html body .employees-page .employees-table .employee-status--active {
    background: #C6D300 !important;
    color: #fff !important;
}
html body .employees-page .employees-table .employee-status--inactive {
    background: #64748b !important;
    color: #fff !important;
}
html body .employees-page .employees-table td:first-child,
html body .employees-page .employees-table td:nth-child(2),
html body .employees-page .employees-table td.employees-email,
html body .employees-page .employees-table td.employees-dept,
html body .employees-page .employees-table td.employees-assigned {
    white-space: normal !important;
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
    text-align: left !important;
    line-height: 1.35 !important;
    max-width: none !important;
}
html body .employees-page .employees-table th { font-size:.78rem !important; }
html body .employees-page .employees-table-wrap {
    overflow-x: auto !important;
    overflow-y: visible !important;
}
html body .employees-page .employees-table {
    width: 100% !important;
    min-width: 960px !important;
    table-layout: fixed !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
@media (min-width: 769px) {
    html body .employees-page .employees-table th:nth-child(1),
    html body .employees-page .employees-table td:nth-child(1) { width: 13% !important; }
    html body .employees-page .employees-table th:nth-child(2),
    html body .employees-page .employees-table td:nth-child(2) { width: 13% !important; }
    html body .employees-page .employees-table th:nth-child(3),
    html body .employees-page .employees-table td:nth-child(3) { width: 24% !important; }
    html body .employees-page .employees-table th:nth-child(4),
    html body .employees-page .employees-table td:nth-child(4) { width: 11% !important; }
    html body .employees-page .employees-table th:nth-child(5),
    html body .employees-page .employees-table td:nth-child(5) { width: 12% !important; }
    html body .employees-page .employees-table th:nth-child(6),
    html body .employees-page .employees-table td:nth-child(6) { width: 10% !important; }
    html body .employees-page .employees-table th:nth-child(7),
    html body .employees-page .employees-table td:nth-child(7) { width: 9% !important; }
    html body .employees-page .employees-table th:nth-child(8),
    html body .employees-page .employees-table td:nth-child(8) { width: 8% !important; }
}
html body .employees-page .employees-table thead,
html body .employees-page .employees-table th {
    position: sticky !important;
    top: 0 !important;
    z-index: 4 !important;
    background: #2379b0 !important;
}

@media (max-width: 768px) {
    html body .employees-page .employees-header {
        flex-direction: column !important;
        align-items: stretch !important;
        padding-right: 0 !important;
    }
    html body .employees-page .add-employee-button {
        width: 100% !important;
        box-sizing: border-box !important;
    }
    html body .employees-page .employees-table-wrap {
        overflow: visible !important;
        background: transparent !important;
    }
    html body .employees-page .employees-table {
        display: block !important;
        min-width: 0 !important;
        width: 100% !important;
    }
    html body .employees-page .employees-table thead {
        display: none !important;
    }
    html body .employees-page .employees-table tbody {
        display: block !important;
        width: 100% !important;
    }
    html body .employees-page .employees-table tbody tr {
        display: block !important;
        width: 100% !important;
        margin: 0 0 14px !important;
        border: 2px solid #9aa9ff !important;
        background: #fff !important;
        box-sizing: border-box !important;
    }
    html body .employees-page .employees-table td {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        padding: 10px 12px !important;
        text-align: right !important;
        white-space: normal !important;
        border: 0 !important;
        border-bottom: 1px solid #dbe3f5 !important;
        position: static !important;
    }
    html body .employees-page .employees-table td:last-child {
        border-bottom: 0 !important;
    }
    html body .employees-page .employees-table td::before {
        content: attr(data-label);
        flex: 0 0 42%;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #2379b0;
    }
    html body .employees-page .employees-table td.employees-empty {
        display: block !important;
        text-align: center !important;
    }
    html body .employees-page .employees-table td.employees-empty::before {
        content: none;
    }
    html body .employees-page .employees-table td .employee-actions {
        flex-direction: row !important;
        justify-content: flex-end !important;
        width: auto !important;
    }
}
</style>

<div class="employees-page">
    <div class="employees-header">
        <h1>Employees List</h1>
       
                <a href="{{ route('manager.employees.create') }}" class="add-employee-button" style="background-color: #cbe212 !important; border: 1px solid #b5cb0e !important; color: #0a1347 !important; border-radius: 6px !important; padding: 9px 16px !important; font-weight: 700 !important; font-size: 0.88rem !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; box-shadow: none !important; transition: background-color 0.15s ease !important;">+ Add Employee</a>
    </div>

    <div class="employees-table-wrap">
        <table class="employees-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Assigned to / Manages</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td data-label="First name" title="{{ $employee->first_name }}">{{ $employee->first_name }}</td>
                        <td data-label="Last name" title="{{ $employee->last_name }}">{{ $employee->last_name }}</td>
                        <td class="employees-email" data-label="Email" title="{{ $employee->email }}">{{ $employee->email }}</td>
                        <td class="employees-dept" data-label="Department">{{ $employee->department ?: '—' }}</td>
                        <td class="employees-assigned" data-label="Assigned to / Manages">
                            @if(strtolower((string) ($employee->role ?? '')) === 'manager')
                                @php $managed = $employee->managedDepartments->pluck('department')->filter()->values(); @endphp
                                @if($managed->isNotEmpty())
                                    {{ $managed->implode(', ') }}
                                @else
                                    —
                                @endif
                            @elseif($employee->manager)
                                {{ trim(($employee->manager->first_name ?? '').' '.($employee->manager->last_name ?? '')) ?: '—' }}
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="Role">{{ \App\Support\RoleLabels::display($employee->role) }}</td>
                        <td data-label="Status">
                            <span class="employee-status {{ $employee->is_active ? 'employee-status--active' : 'employee-status--inactive' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td data-label="Action">
                            <div class="employee-actions">
    <a href="{{ route('manager.employees.edit', $employee->id) }}" class="edit-button" title="Edit" aria-label="Edit employee">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
    </a>
    <form id="delete-form-{{ $employee->id }}" action="{{ route('manager.employees.destroy', $employee->id) }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    <button type="button" class="delete-button delete-employee-btn" data-employee-id="{{ $employee->id }}" title="Delete" aria-label="Delete employee">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
    </button>
</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="employees-empty">No employees to show.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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