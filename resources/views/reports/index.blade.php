@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 pb-32">
    {{-- Header --}}
    <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); position: sticky; top: 0; z-index: 30;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 1rem 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text); margin: 0;">
                        Activity Reports
                    </h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.25rem;">
                        Manage monthly activity reports and invoices
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button onclick="openCreateModal()"
                            style="padding: 0.625rem 1rem; background: var(--theme-primary); color: white; border: none; border-radius: 0.5rem; font-weight: 600; font-size: var(--theme-font-size); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-plus"></i>
                        Create New Report
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div style="max-width: 1400px; margin: 0 auto; padding: 1.5rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div style="background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- Statistics Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: white; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 0.75rem; padding: 1rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 0.75rem; color: var(--theme-text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Total Reports</p>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text); margin: 0.25rem 0 0 0;">{{ $stats['total'] }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: rgba(var(--theme-primary-rgb), 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-alt" style="color: var(--theme-primary); font-size: 1.125rem;"></i>
                    </div>
                </div>
            </div>

            <div style="background: white; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 0.75rem; padding: 1rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 0.75rem; color: var(--theme-text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Draft</p>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text); margin: 0.25rem 0 0 0;">{{ $stats['draft'] }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: rgba(251, 191, 36, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-edit" style="color: #f59e0b; font-size: 1.125rem;"></i>
                    </div>
                </div>
            </div>

            <div style="background: white; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 0.75rem; padding: 1rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 0.75rem; color: var(--theme-text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Finalized</p>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text); margin: 0.25rem 0 0 0;">{{ $stats['finalized'] }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: rgba(34, 197, 94, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle" style="color: #22c55e; font-size: 1.125rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div style="background: white; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem;">
            <form method="GET" action="{{ route('reports.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem;">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, project, customer..."
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.375rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                </div>

                <div style="min-width: 150px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem;">Status</label>
                    <select name="status" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.375rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalized</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <div style="min-width: 200px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem;">Customer</label>
                    <select name="customer_id" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.375rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" style="padding: 0.5rem 1rem; background: var(--theme-primary); color: white; border: none; border-radius: 0.375rem; font-weight: 500; font-size: var(--theme-font-size); cursor: pointer;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.index') }}" style="padding: 0.5rem 1rem; background: var(--theme-surface); color: var(--theme-text); border: 1px solid var(--theme-border); border-radius: 0.375rem; font-weight: 500; font-size: var(--theme-font-size); text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Reports Table --}}
        <div style="background: white; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 0.75rem; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--theme-surface); border-bottom: 1px solid var(--theme-border);">
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Invoice #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Customer</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Project</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Period</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Total</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr style="border-bottom: 1px solid var(--theme-border); transition: background 0.15s;"
                            onmouseover="this.style.background='var(--theme-surface)'"
                            onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem; font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $report->invoice_number }}
                            </td>
                            <td style="padding: 1rem; color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $report->project->customer->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 1rem; color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $report->project->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 1rem; color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                @if($report->period_start && $report->period_end)
                                    {{ date('M Y', strtotime($report->period_start)) }}
                                @else
                                    {{ date('M Y', strtotime($report->invoice_date)) }}
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @php
                                    $statusColors = [
                                        'draft' => 'background: #fef3c7; color: #92400e; border-color: #fbbf24;',
                                        'finalized' => 'background: #dbeafe; color: #1e40af; border-color: #3b82f6;',
                                        'sent' => 'background: #e0e7ff; color: #3730a3; border-color: #6366f1;',
                                        'paid' => 'background: #d1fae5; color: #065f46; border-color: #10b981;',
                                    ];
                                @endphp
                                <span style="display: inline-block; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; border: 1px solid; {{ $statusColors[$report->status] ?? 'background: #f3f4f6; color: #374151; border-color: #d1d5db;' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right; font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">
                                €{{ number_format($report->total_inc_vat ?? 0, 2, ',', '.') }}
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                    <a href="{{ route('reports.spreadsheet', $report) }}"
                                       style="padding: 0.375rem 0.75rem; background: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;"
                                       title="Open Report View">
                                        <i class="fas fa-table"></i>
                                        Open
                                    </a>
                                    @if($report->status === 'draft')
                                    <a href="{{ route('reports.finalize-view', $report) }}"
                                       style="padding: 0.375rem 0.75rem; background: #22c55e; color: white; border-radius: 0.375rem; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;"
                                       title="Finalize Report">
                                        <i class="fas fa-check"></i>
                                        Finalize
                                    </a>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <form action="{{ route('reports.destroy', $report) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this draft report? All time entries will be reset and the report will be permanently deleted.')"
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="padding: 0.375rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;"
                                                title="Delete Draft Report">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                    @else
                                    <a href="{{ route('reports.finalize-view', $report) }}"
                                       style="padding: 0.375rem 0.75rem; background: var(--theme-surface); color: var(--theme-text); border: 1px solid var(--theme-border); border-radius: 0.375rem; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;"
                                       title="View Finalized Report">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem; text-align: center; color: var(--theme-text-muted);">
                                <i class="fas fa-file-alt" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                                <p style="font-size: 1.125rem; font-weight: 500; margin: 0;">No reports found</p>
                                <p style="font-size: var(--theme-font-size); margin-top: 0.5rem;">Create your first report to get started</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reports->hasPages())
            <div style="padding: 1rem; border-top: 1px solid var(--theme-border);">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Create Report Modal --}}
<div id="createReportModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 50; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 0.75rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--theme-border);">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--theme-text); margin: 0;">Create New Report</h3>
            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.25rem;">Select customer and project to generate activity report</p>
        </div>

        <form method="POST" action="{{ route('reports.store') }}" style="padding: 1.5rem;">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Customer <span style="color: #ef4444;">*</span>
                </label>
                <select name="customer_id" id="modal_customer_id" required
                        onchange="loadProjectsForCustomer(this.value)"
                        style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                    <option value="">Select a customer...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Project <span style="color: #ef4444;">*</span>
                </label>
                <select name="project_id" id="modal_project_id" required
                        onchange="updatePeriodFromProject()"
                        style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                    <option value="">Select customer first...</option>
                </select>
                <p style="font-size: 0.75rem; color: var(--theme-text-muted); margin-top: 0.25rem;">
                    <i class="fas fa-info-circle"></i> Period dates will be filled from project dates
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Period Start <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="period_start" id="modal_period_start" required
                           value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                           style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Period End <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="period_end" id="modal_period_end" required
                           value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                           style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--theme-border); border-radius: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeCreateModal()"
                        style="padding: 0.625rem 1.25rem; background: var(--theme-surface); color: var(--theme-text); border: 1px solid var(--theme-border); border-radius: 0.5rem; font-weight: 500; font-size: var(--theme-font-size); cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 0.625rem 1.25rem; background: var(--theme-primary); color: white; border: none; border-radius: 0.5rem; font-weight: 600; font-size: var(--theme-font-size); cursor: pointer;">
                    <i class="fas fa-plus-circle"></i> Create Report
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('createReportModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createReportModal').style.display = 'none';
    document.getElementById('modal_customer_id').value = '';
    document.getElementById('modal_project_id').innerHTML = '<option value="">Select customer first...</option>';
    // Reset to current month
    const now = new Date();
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    document.getElementById('modal_period_start').value = startOfMonth.toISOString().split('T')[0];
    document.getElementById('modal_period_end').value = endOfMonth.toISOString().split('T')[0];
}

// Store projects data globally
let projectsData = [];

function loadProjectsForCustomer(customerId) {
    const projectSelect = document.getElementById('modal_project_id');

    if (!customerId) {
        projectSelect.innerHTML = '<option value="">Select customer first...</option>';
        projectsData = [];
        return;
    }

    // Show loading
    projectSelect.innerHTML = '<option value="">Loading projects...</option>';
    projectSelect.disabled = true;

    // Fetch projects via AJAX
    fetch(`/api/reports/customers/${customerId}/projects`)
        .then(response => response.json())
        .then(projects => {
            projectsData = projects; // Store globally

            if (projects.length === 0) {
                projectSelect.innerHTML = '<option value="">No projects found for this customer</option>';
            } else {
                projectSelect.innerHTML = '<option value="">Select a project...</option>';
                projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = `${project.name} (${project.status})`;
                    // Store dates as data attributes
                    option.dataset.startDate = project.start_date || '';
                    option.dataset.endDate = project.end_date || '';
                    projectSelect.appendChild(option);
                });
            }
            projectSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading projects:', error);
            projectSelect.innerHTML = '<option value="">Error loading projects</option>';
            projectSelect.disabled = false;
        });
}

function updatePeriodFromProject() {
    const projectSelect = document.getElementById('modal_project_id');
    const periodStartInput = document.getElementById('modal_period_start');
    const periodEndInput = document.getElementById('modal_period_end');

    const selectedOption = projectSelect.options[projectSelect.selectedIndex];

    if (!selectedOption || !selectedOption.value) {
        return;
    }

    const startDate = selectedOption.dataset.startDate;
    const endDate = selectedOption.dataset.endDate;

    // Helper function to format date to yyyy-MM-dd
    function formatDateForInput(dateString) {
        if (!dateString) return null;
        // Extract just the date part (yyyy-MM-dd) from ISO string
        return dateString.split('T')[0];
    }

    // Update period fields if project has dates
    if (startDate) {
        periodStartInput.value = formatDateForInput(startDate);
    }

    if (endDate) {
        periodEndInput.value = formatDateForInput(endDate);
    } else if (startDate) {
        // If no end date, use start date + 1 month as default
        const start = new Date(startDate);
        const end = new Date(start.getFullYear(), start.getMonth() + 1, 0); // Last day of month
        periodEndInput.value = end.toISOString().split('T')[0];
    }
}

// Close modal when clicking outside
document.getElementById('createReportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
    }
});
</script>
@endpush
@endsection
