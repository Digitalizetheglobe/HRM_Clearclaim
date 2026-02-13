@extends('layouts.admin')

@section('page-title')
    {{ __('Hierarchy Chart') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Hierarchy Chart') }}</li>
@endsection

@push('styles')
<style>
.executive-level {
    text-align: center;
}

.executive-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.executive-box:hover {
    transform: translateY(-5px);
}

.ceo-level {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.c-level {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.executive-avatar {
    font-size: 3rem;
    margin-bottom: 10px;
}

.executive-title {
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.executive-dept {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 5px;
}

.executive-name {
    font-size: 0.85rem;
}

.department-hierarchy {
    margin-top: 30px;
}

.hierarchy-level {
    margin-bottom: 30px;
}

.level-title {
    text-align: center;
    font-weight: bold;
    color: #495057;
    margin-bottom: 20px;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.employee-box {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    margin: 0 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.employee-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.employee-box.current-user {
    border-color: #007bff;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.employee-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.employee-avatar {
    font-size: 2rem;
    margin-right: 15px;
}

.employee-info {
    flex: 1;
}

.employee-name {
    font-weight: bold;
    color: #495057;
    margin-bottom: 5px;
}

.employee-designation {
    color: #6c757d;
    font-size: 0.9rem;
}

.subordinates-info, .peers-info {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

.legend {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-top: 30px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.legend-box {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    margin-right: 10px;
    display: inline-block;
}

.legend-box.current-user {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #007bff;
}

.legend-box.regular-employee {
    background: white;
    border: 2px solid #e9ecef;
}

hr {
    border: 0;
    height: 2px;
    background: linear-gradient(to right, transparent, #ccc, transparent);
    margin: 40px 0;
}

@media (max-width: 768px) {
    .executive-box {
        margin-bottom: 15px;
    }
    
    .employee-box {
        margin: 5px 0;
    }
}
</style>
@endpush

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
                        <div class="executive-level mb-5">
                            <div class="row justify-content-center">
                                <div class="col-md-2 text-center">
                                    <div class="executive-box ceo-level">
                                        <div class="executive-avatar">
                                            👨‍💼
                                        </div>
                                        <div class="executive-title">CEO</div>
                                        @if($hierarchyData['executive_level']['CEO'])
                                            <div class="executive-name">{{ $hierarchyData['executive_level']['CEO']->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row justify-content-center mt-4">
                                @foreach(['COO', 'CFO', 'CHRO', 'CSO', 'CAO'] as $executive)
                                    @if($hierarchyData['executive_level'][$executive])
                                        <div class="col-md-2 text-center mb-3">
                                            <div class="executive-box c-level">
                                                <div class="executive-avatar">
                                                    {{ $executive == 'CHRO' ? '👩‍💼' : '👨‍💼' }}
                                                </div>
                                                <div class="executive-title">{{ $executive }}</div>
                                                <div class="executive-dept">
                                                    {{ App\Http\Controllers\HierarchyChartController::getExecutiveDepartment($executive) }}
                                                </div>
                                                <div class="executive-name">
                                                    {{ $hierarchyData['executive_level'][$executive]->name }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-5">

                        <!-- Department Hierarchy -->
                        <div class="department-hierarchy">
                            <h4 class="text-center mb-4">
                                <i class="fas fa-building me-2"></i>
                                {{ $department->name }} DIVISION
                            </h4>

                            @foreach($hierarchyData['hierarchy_levels'] as $level => $employees)
                                <div class="hierarchy-level mb-4">
                                    <div class="level-title">
                                        {{ App\Http\Controllers\HierarchyChartController::getLevelName($level) }}
                                    </div>
                                    
                                    <div class="row justify-content-center">
                                        @foreach($employees as $employeeData)
                                            <div class="col-md-3 mb-3">
                                                <div class="employee-box {{ $employeeData['is_current_user'] ? 'current-user' : '' }}">
                                                    <div class="employee-header">
                                                        <div class="employee-avatar">
                                                            {{ $employeeData['is_current_user'] ? '👤' : '👥' }}
                                                        </div>
                                                        <div class="employee-info">
                                                            <div class="employee-name">
                                                                {{ $employeeData['employee']->name }}
                                                                @if($employeeData['is_current_user'])
                                                                    <span class="badge bg-primary ms-2">YOU</span>
                                                                @endif
                                                            </div>
                                                            <div class="employee-designation">
                                                                {{ $employeeData['employee']->designation->name ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if(!empty($employeeData['subordinates']))
                                                        <div class="subordinates-info">
                                                            <small class="text-muted">
                                                                <i class="fas fa-users me-1"></i>
                                                                {{ count($employeeData['subordinates']) }} Direct Reports
                                                            </small>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(!empty($employeeData['peers']))
                                                        <div class="peers-info">
                                                            <small class="text-muted">
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
