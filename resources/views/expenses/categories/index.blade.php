@extends('layouts.admin')

@section('page-title')
    {{ __('Expense Categories') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Categories') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('expense-categories.create') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i> {{ __('Create Category') }}
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ Str::limit($category->description ?? '-', 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $category->status == 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('expense-categories.edit', $category->id) }}" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('expense-categories.destroy', $category->id) }}" 
                                              class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger text-white">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No categories found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




