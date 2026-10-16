@extends($layout ?? 'layouts.manager')

@section('title', 'Employees List')

@section('content')
<style>
    html body main:has(.employees-page) {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .employees-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 4px;
        color: #1e293b;
        flex: 1 1 auto;
        min-height: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    .employees-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        flex: 0 0 auto;
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
    .add-employee-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #b2cd16;
        border: 1px solid #91bf14;
        color: #0a1347;
        padding: 9px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.88rem;
        white-space: nowrap;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .add-employee-button:hover {
        background-color: #9eb812;
        color: #0a1347;
    }
    .flash-message {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-weight: 600;
        font-size: 0.92rem;
        flex: 0 0 auto;
    }
    .flash-message--success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #86efac;
    }
    .flash-message--error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .employees-table-wrap {
        position: relative;
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
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
        font-size: 0.92rem;
        min-width: 880px;
    }
    .employees-table th,
    .employees-table td {
        padding: 14px 16px;
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
        letter-spacing: 0.075em;
        white-space: nowrap;
        border-bottom-color: #A4B4F6;
        border-right-color: #A4B4F6;
        box-shadow: 0 2px 0 #2A6FA1;
    }
    .employees-table th:first-child,
    .employees-table td:first-child {
        padding-left: 20px;
    }
    .employees-table td:first-child,
    .employees-table td:nth-child(2) {
        color: #000;
        font-weight: 700;
        white-space: nowrap;
    }
    .employees-table tbody tr {
        background: #d3e5df;
    }
    .employees-table tbody tr:hover {
        background: #bfd9d0;
    }
    .employees-table tbody tr:last-child td {
        border-bottom: none;
    }
    .employees-email {
        overflow-wrap: anywhere;
        word-break: break-word;
        max-width: 220px;
    }
    .employees-dept,
    .employees-assigned {
        overflow-wrap: anywhere;
        word-break: break-word;
        max-width: 160px;
        line-height: 1.35;
    }
    .employee-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 5.5rem;
        padding: 5px 10px;
        border-radius: 999px;
        font-weight: 750;
        font-size: 0.76rem;
        color: #fff;
        letter-spacing: 0.01em;
    }
    .employee-status--active {
        background: #C6D300;
    }
    .employee-status--inactive {
        background: #64748b;
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
    @media (max-width: 768px) {
        .employees-page h1 {
            font-size: 1.75rem;
        }
        .add-employee-button {
            width: 100%;
        }
        .employee-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .edit-button,
        .delete-button {
            width: 100%;
        }
    }
    html body .employees-page .employees-table td .employee-actions a.edit-button,
html body .employees-page .employees-table td .employee-actions button.delete-button {
    background:#fff !important; background-color:#fff !important; background-image:none !important;
    border:1.5px solid #333 !important; color:#222 !important;
    border-radius:9px !important; width:34px !important; height:34px !important;
    min-width:0 !important; padding:0 !important; box-shadow:none !important;
    display:inline-flex !important; align-items:center; justify-content:center;
}
html body .employees-page .employees-table td .employee-actions a.edit-button:hover,
html body .employees-page .employees-table td .employee-actions button.delete-button:hover {
    background:#eef1f6 !important; color:#000 !important;
}
html body .employees-page .employees-table td .employee-actions svg { stroke:#222 !important; }

/* header text slightly larger like the prototype */
html body .employees-page .employees-table th { font-size:.78rem !important; }
html body .employees-page .employees-table-wrap { overflow: auto !important; }
html body .employees-page .employees-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
html body .employees-page .employees-table thead,
html body .employees-page .employees-table th {
    position: sticky !important;
    top: 0 !important;
    z-index: 4 !important;
    background: #2379b0 !important;
}
</style>

<div class="employees-page">
    <div class="employees-header">
        <h1>Employees List</h1>
        <a href="{{ route('manager.employees.create') }}" class="add-employee-button">+ Add Employee</a>
    </div>

    @if(session('success'))
        <div class="flash-message flash-message--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-message flash-message--error">{{ session('error') }}</div>
    @endif

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
                        <td>{{ $employee->first_name }}</td>
                        <td>{{ $employee->last_name }}</td>
                        <td class="employees-email">{{ $employee->email }}</td>
                        <td class="employees-dept">{{ $employee->department ?: '—' }}</td>
                        <td class="employees-assigned">
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
                        <td>{{ \App\Support\RoleLabels::display($employee->role) }}</td>
                        <td>
                            <span class="employee-status {{ $employee->is_active ? 'employee-status--active' : 'employee-status--inactive' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
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
