@extends('layouts.admin')

@section('page-title')
    {{ __('Hierarchy Chart') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Hierarchy Chart') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-chart-hierarchy me-2"></i>
                        {{ __('Organization Hierarchy Chart') }}
                    </h5>
                    <small class="text-muted">
                        {{ __('Department: :department', ['department' => $department->name]) }}
                    </small>
                </div>
                <div class="card-body">
                    @if(isset($hierarchyData))
                        <!-- Executive Level -->
                        <div class="text-center mb-12 relative" style="min-height: 300px;">
                            <!-- CEO Card -->
                            <div class="row justify-content-center mb-10">
                                <div class="col-md-3 text-center">
                                    <div class="bg-gradient-to-br from-blue-50 to-white border-2 border-blue-300 rounded-xl p-4 mx-1 mb-3 shadow-lg transition-all duration-300 min-h-36 flex flex-col relative overflow-hidden hover:-translate-y-2 hover:shadow-xl hover:border-blue-400 z-10">
                                        <div class="absolute top-2 right-2 text-2xl">👔</div>
                                        <div class="font-bold text-gray-800 mb-2 text-lg text-center">
                                            CEO
                                        </div>
                                        @if(isset($hierarchyData['executive_level']['CEO']) && $hierarchyData['executive_level']['CEO'])
                                            <div class="text-gray-700 text-base text-center font-medium mb-3">
                                                {{ $hierarchyData['executive_level']['CEO']->name }}
                                            </div>
                                        @else
                                            <div class="text-gray-500 text-base text-center font-medium mb-3 opacity-75">
                                                No CEO
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Connecting Lines -->
                            <div class="absolute left-1/2 transform -translate-x-1/2 top-40 w-1 h-24 bg-gray-500 z-0"></div>
                            <div class="absolute left-1/2 transform -translate-x-1/2 top-64 w-3/4 h-1 bg-gray-500 z-0"></div>
                            
                            <!-- Executive Cards with Lines -->
                            <div class="row justify-content-center mt-16 relative">
                                @foreach(['COO', 'CFO', 'CHRO', 'CSO', 'CAO'] as $index => $executive)
                                    <div class="col-md-3 text-center relative">
                                        <!-- Vertical connecting line -->
                                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -top-20 w-1 h-20 bg-gray-500 z-0"></div>
                                        <!-- Connection node -->
                                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -top-2 w-3 h-3 bg-gray-500 rounded-full z-10"></div>
                                        
                                        <div class="bg-white border border-gray-300 rounded-lg p-3 mx-1 mb-2 shadow-md transition-all duration-300 min-h-32 flex flex-col relative overflow-hidden hover:-translate-y-1 hover:shadow-lg hover:border-blue-300 z-10">
                                            <div class="font-bold text-gray-700 mb-1 text-ms text-center">
                                                {{ $executive }}
                                            </div>
                                            <div class="text-gray-600 text-md text-center font-medium mb-2">
                                                {{ App\Http\Controllers\HierarchyChartController::getExecutiveDepartment($executive) }}
                                            </div>
                                            @if(isset($hierarchyData['executive_level'][$executive]) && $hierarchyData['executive_level'][$executive])
                                                <div class="text-gray-600 text-5md text-center font-medium mb-2">
                                                    {{ $hierarchyData['executive_level'][$executive]->name }}
                                                </div>
                                            @else
                                                <div class="text-gray-600 text-md text-center font-medium mb-2 opacity-75">
                                                    No {{ $executive }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-0 h-0.5 bg-gradient-to-r from-transparent via-gray-300 to-transparent my-10">

                        <!-- Department Hierarchy -->
                        <div class="mt-8">
                            <h4 class="text-center mb-4">
                                <i class="fas fa-building me-2"></i>
                                {{ $department->name }} DIVISION
                            </h4>

                            @foreach($hierarchyData['hierarchy_levels'] as $level => $employees)
                                <div class="mb-8">
                                    <div class="text-center font-bold text-gray-700 mb-5 text-lg uppercase tracking-wide flex items-center justify-center min-h-10">
                                        {{ App\Http\Controllers\HierarchyChartController::getLevelName($level) }}
                                    </div>
                                    
                                    <div class="row justify-content-center">
                                        @foreach($employees as $employeeData)
                                            <div class="col-md-3 mb-3">
                                                <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-200 rounded-lg p-3 mx-1 mb-2 shadow-md transition-all duration-300 min-h-32 flex flex-col relative overflow-hidden hover:-translate-y-1 hover:shadow-lg hover:border-blue-300 {{ $employeeData['is_current_user'] ? 'border-blue-500 bg-gradient-to-br from-blue-100 to-blue-200 shadow-lg' : '' }}">
                                                    <div class="flex items-center mb-2 text-center flex-col">
                                                        <div class="text-2xl mb-1">
                                                            {{ $employeeData['is_current_user'] ? '👤' : '👥' }}
                                                        </div>
                                                        <div class="text-center w-full">
                                                            <div class="font-semibold text-gray-700 mb-1 text-sm text-center">
                                                                {{ $employeeData['employee']->name }}
                                                                @if($employeeData['is_current_user'])
                                                                    <span class="badge bg-primary ms-1 text-xs">YOU</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-gray-600 text-xs text-center font-medium mb-2">
                                                                {{ $employeeData['employee']->designation->name ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if(!empty($employeeData['subordinates']))
                                                        <div class="mt-auto pt-1 border-t border-gray-200 text-center">
                                                            <small class="text-muted text-xs">
                                                                <i class="fas fa-users me-1"></i>
                                                                {{ count($employeeData['subordinates']) }} Direct Reports
                                                            </small>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(!empty($employeeData['peers']))
                                                        <div class="mt-auto pt-1 border-t border-gray-200 text-center">
                                                            <small class="text-muted text-xs">
                                                                <i class="fas fa-user-friends me-1"></i>
                                                                {{ count($employeeData['peers']) }} Peers
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <!-- <div class="legend mt-5">
                            <h6>{{ __('Legend') }}</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="legend-item">
                                        <span class="legend-box current-user"></span>
                                        {{ __('Your Position') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="legend-item">
                                        <span class="legend-box regular-employee"></span>
                                        {{ __('Other Employees') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="legend-item">
                                        <i class="fas fa-users me-1"></i>
                                        {{ __('Direct Subordinates') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="legend-item">
                                        <i class="fas fa-user-friends me-1"></i>
                                        {{ __('Peers at Same Level') }}
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No hierarchy data available for your department.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
