@extends('layouts.admin')

@section('page-title')
    {{ __('Add New Expense') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">{{ __('My Expenses') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add New Expense') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Expense Request Form') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" 
                                       value="{{ old('amount') }}" required>
                                @error('amount')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Expense Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" 
                                       value="{{ old('expense_date') }}" required>
                                @error('expense_date')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('Receipt File(s)') }}</label>
                                <input type="file" name="receipt_file[]" class="form-control" 
                                       accept="image/*,application/pdf" multiple>
                                <small class="text-muted">{{ __('You can upload multiple files (JPG, PNG, PDF). Max 10MB per file.') }}</small>
                                @error('receipt_file.*')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="4" 
                                          placeholder="{{ __('Enter expense description...') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end">
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Expense Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




