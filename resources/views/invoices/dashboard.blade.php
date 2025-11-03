@extends('layouts.app')

@section('title', 'Invoice Generation Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoice Generation Dashboard</h1>
                    <p class="text-sm text-gray-600">Overview of projects ready for invoicing</p>
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Month Selector --}}
                    <form method="GET" action="{{ route('invoices.dashboard') }}" class="flex items-center space-x-2">
                        <label class="text-sm text-slate-600">Period:</label>
                        <select name="month" onchange="this.form.submit()" 
                                class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            @foreach($monthOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        
                        @if(Auth::user()->role === 'super_admin' && $companies->count() > 1)
                        <select name="company_id" onchange="this.form.submit()"
                                class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $selectedCompany == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </form>
                    
                    {{-- Refresh Button --}}
                    <button onclick="window.location.reload()" 
                            class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-sync-alt mr-1.5 text-xs"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert for Missed Invoices --}}
    @php
        $totalMissedCount = 0;
        $totalMissedAmount = 0;
        $missedProjects = [];
        foreach($invoiceData as $companyData) {
            foreach($companyData['customers'] as $customerData) {
                foreach($customerData['projects'] as $projectData) {
                    if($projectData['has_missed_invoices']) {
                        $totalMissedCount += count($projectData['missed_periods']);
                        $totalMissedAmount += $projectData['missed_total_amount'];
                        $missedProjects[] = [
                            'name' => $projectData['project']->name,
                            'amount' => $projectData['missed_total_amount']
                        ];
                    }
                }
            }
        }
    @endphp
    
    @if($totalMissedCount > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-red-800">
                        Attention Required: {{ $totalMissedCount }} Missed {{ $totalMissedCount == 1 ? 'Invoice' : 'Invoices' }} Detected - Total Amount: €{{ number_format($totalMissedAmount, 2, ',', '.') }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>The following projects have overdue invoice periods that need immediate attention:</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($missedProjects as $project)
                            <li>{{ $project['name'] }} - €{{ number_format($project['amount'], 2, ',', '.') }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 font-medium">Total missed revenue: <span class="text-red-900 text-base">€{{ number_format($totalMissedAmount, 2, ',', '.') }}</span></p>
                        <p class="mt-1 font-medium">Please generate these missing invoices before processing new ones.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @php
                $totalProjects = 0;
                $totalAmount = 0;
                $totalHours = 0;
                $readyCount = 0;
                $missedCount = 0;
                
                foreach($invoiceData as $companyData) {
                    $totalProjects += $companyData['project_count'];
                    $totalAmount += $companyData['total_amount'];
                    $totalHours += $companyData['total_hours'];
                    foreach($companyData['customers'] as $customerData) {
                        foreach($customerData['projects'] as $projectData) {
                            if($projectData['ready_for_invoice']) {
                                $readyCount++;
                            }
                            if($projectData['has_missed_invoices']) {
                                $missedCount += count($projectData['missed_periods']);
                            }
                        }
                    }
                }
            @endphp
            
            {{-- Missed Invoices Card - IMPORTANT! --}}
            @if($missedCount > 0)
            <div class="bg-white/60 backdrop-blur-sm border-2 border-red-400 rounded-xl p-4 animate-pulse">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-red-600 font-bold">⚠️ MISSED INVOICES</p>
                        <p class="text-2xl font-bold text-red-700 mt-1">€{{ number_format($totalMissedAmount, 0, ',', '.') }}</p>
                        <p class="text-xs text-red-500 mt-1">{{ $missedCount }} overdue {{ $missedCount == 1 ? 'period' : 'periods' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Ready for Invoice Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-600 font-medium">Ready for Invoice</p>
                        <p class="text-2xl font-bold text-green-700 mt-1">{{ $readyCount }}</p>
                        <p class="text-xs text-slate-500 mt-1">Projects</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>
            
            {{-- Total Projects Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Projects</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalProjects }}</p>
                        <p class="text-xs text-slate-500 mt-1">This period</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-slate-600"></i>
                    </div>
                </div>
            </div>
            
            {{-- Total Amount Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Total Amount</p>
                        <p class="text-2xl font-bold text-blue-700 mt-1">€{{ number_format($totalAmount, 2, ',', '.') }}</p>
                        <p class="text-xs text-slate-500 mt-1">To invoice</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-euro-sign text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            {{-- Total Hours Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-purple-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-purple-600 font-medium">Total Hours</p>
                        <p class="text-2xl font-bold text-purple-700 mt-1">{{ number_format($totalHours, 1) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Billable</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if(empty($invoiceData))
            {{-- No Data State --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900 mb-2">No Projects Ready for Invoicing</h3>
                <p class="text-sm text-slate-600">There are no projects that require invoice generation for the selected period.</p>
            </div>
        @else
            {{-- Invoice Data by Company --}}
            @foreach($invoiceData as $companyId => $companyData)
            <div class="mb-6">
                {{-- Company Header --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-t-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200/60">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ $companyData['company']->name }}
                                </h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    {{ $companyData['project_count'] }} {{ $companyData['project_count'] == 1 ? 'project' : 'projects' }} • 
                                    €{{ number_format($companyData['total_amount'], 2, ',', '.') }} total
                                </p>
                            </div>
                            @if($readyCount > 0)
                            <button onclick="generateCompanyInvoices({{ $companyId }})" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                                <i class="fas fa-file-invoice mr-2"></i>
                                Generate All Invoices
                            </button>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Customers --}}
                    <div class="divide-y divide-slate-200/50">
                        @foreach($companyData['customers'] as $customerId => $customerData)
                        <div class="px-6 py-4">
                            {{-- Customer Header --}}
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-medium text-slate-900">
                                        {{ $customerData['customer']->name }}
                                    </h3>
                                    <p class="text-sm text-slate-600">
                                        {{ count($customerData['projects']) }} {{ count($customerData['projects']) == 1 ? 'project' : 'projects' }} • 
                                        €{{ number_format($customerData['subtotal_amount'], 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Projects Table --}}
                            <div class="bg-slate-50/50 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-slate-200/50">
                                    <thead class="bg-slate-100/50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Project</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Billing</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Hours</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white/40 divide-y divide-slate-200/30">
                                        @foreach($customerData['projects'] as $projectData)
                                        <tr class="hover:bg-slate-50/50 transition-colors {{ $projectData['has_missed_invoices'] ? 'bg-red-50/30' : '' }}">
                                            <td class="px-4 py-3">
                                                <div>
                                                    <a href="{{ route('projects.show', $projectData['project']) }}" 
                                                       class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                                        {{ $projectData['project']->name }}
                                                    </a>
                                                    @if($projectData['has_missed_invoices'])
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-800 border border-red-200">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            {{ count($projectData['missed_periods']) }} missed {{ count($projectData['missed_periods']) == 1 ? 'invoice' : 'invoices' }}
                                                        </span>
                                                        <div class="text-xs text-red-600 mt-1">
                                                            @foreach($projectData['missed_periods'] as $missed)
                                                                <div>• {{ $missed['month_label'] }} - €{{ number_format($missed['amount'], 2, ',', '.') }} ({{ $missed['days_overdue'] }} days overdue)</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-xs text-slate-600">
                                                    {{ ucfirst($projectData['project']->billing_frequency) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-slate-900">
                                                    {{ number_format($projectData['total_hours'], 1) }}h
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium text-slate-900">
                                                    €{{ number_format($projectData['total_amount'], 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($projectData['existing_invoice'])
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Invoice Exists
                                                    </span>
                                                @elseif($projectData['ready_for_invoice'])
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 border border-green-200">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Ready
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                        <i class="fas fa-hourglass-half mr-1"></i>
                                                        Not Yet Due
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($projectData['ready_for_invoice'] && !$projectData['existing_invoice'])
                                                    <button onclick="generateProjectInvoice({{ $projectData['project']->id }})" 
                                                            class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-all">
                                                        Generate
                                                    </button>
                                                @elseif($projectData['existing_invoice'])
                                                    <a href="{{ route('invoices.show', $projectData['existing_invoice']) }}" 
                                                       class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded hover:bg-slate-200 transition-all inline-block">
                                                        View
                                                    </a>
                                                @else
                                                    <span class="text-xs text-slate-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Invoice Generation Modal --}}
<div id="invoiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-file-invoice text-blue-600"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Generate Invoice</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to generate invoice(s) for the selected project(s)?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmGenerate" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" style="box-shadow: var(--theme-card-shadow);">
                    Generate Invoice
                </button>
                <button onclick="closeInvoiceModal()" class="mt-3 px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-md w-full border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300" style="box-shadow: var(--theme-card-shadow);">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let selectedProjectIds = [];

    function generateProjectInvoice(projectId) {
        selectedProjectIds = [projectId];
        document.getElementById('invoiceModal').classList.remove('hidden');
    }

    function generateCompanyInvoices(companyId) {
        // Collect all ready project IDs for this company
        // This would need to be populated from the backend
        alert('Bulk invoice generation will be implemented soon');
    }

    function closeInvoiceModal() {
        document.getElementById('invoiceModal').classList.add('hidden');
        selectedProjectIds = [];
    }

    document.getElementById('confirmGenerate').addEventListener('click', function() {
        if (selectedProjectIds.length > 0) {
            // Submit form to generate invoices
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("invoices.generate") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'month';
            monthInput.value = '{{ $selectedMonth }}';
            form.appendChild(monthInput);
            
            selectedProjectIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'project_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endpush