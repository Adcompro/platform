@extends('layouts.app')

@section('title', 'Quick Reports')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Quick Reports</h1>
                    <p class="text-sm text-slate-600 mt-1">Generate instant reports for quick insights</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Cards Grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Weekly Timesheet Report --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-all">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Weekly Timesheet</h3>
                                <p class="text-sm text-slate-600 mt-1">Detailed time entries per user by week</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Time entries grouped by user and date</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Billable vs non-billable breakdown</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Export to PDF available</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('reports.weekly-timesheet') }}" 
                           class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all text-center">
                            <i class="fas fa-chart-line mr-1"></i>
                            View Report
                        </a>
                        <a href="{{ route('reports.weekly-timesheet', ['week_start' => now()->startOfWeek()->format('Y-m-d')]) }}" 
                           class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-calendar-week mr-1"></i>
                            This Week
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Monthly Invoice Overview --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-all">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-file-invoice-dollar text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Monthly Invoices</h3>
                                <p class="text-sm text-slate-600 mt-1">Overview of all invoices by month</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Invoice status breakdown</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Revenue statistics and trends</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Payment tracking overview</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('reports.monthly-invoices') }}" 
                           class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all text-center">
                            <i class="fas fa-chart-bar mr-1"></i>
                            View Report
                        </a>
                        <a href="{{ route('reports.monthly-invoices', ['month' => now()->format('Y-m')]) }}" 
                           class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-calendar mr-1"></i>
                            This Month
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Project Profitability Report --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-all">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Project Profitability</h3>
                                <p class="text-sm text-slate-600 mt-1">Ranking of most profitable projects</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Revenue vs cost analysis</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Profit margin calculations</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Sortable by profit or margin</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('reports.project-profitability') }}" 
                           class="flex-1 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all text-center">
                            <i class="fas fa-chart-line mr-1"></i>
                            View Report
                        </a>
                        <a href="{{ route('reports.project-profitability', ['period' => 'quarter']) }}" 
                           class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            This Quarter
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Overdue Milestones Report --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-all">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Overdue Milestones</h3>
                                <p class="text-sm text-slate-600 mt-1">List of delayed project milestones</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Urgency level classification</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Days overdue tracking</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Project owner identification</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('reports.overdue-milestones') }}" 
                           class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all text-center">
                            <i class="fas fa-chart-bar mr-1"></i>
                            View Report
                        </a>
                        <a href="{{ route('reports.overdue-milestones', ['days_overdue' => 7]) }}" 
                           class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-filter mr-1"></i>
                            7+ Days
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
        
        {{-- Additional Info --}}
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl mt-1"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Quick Reports Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>These reports provide instant insights into your project management data. All reports can be:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Filtered by date ranges and specific criteria</li>
                            <li>Exported to PDF for sharing and archiving</li>
                            <li>Automatically filtered by your company (for non-admin users)</li>
                            <li>Refreshed in real-time with the latest data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection