@extends('layouts.contractheader')
@section('page-title')
    {{ __('Experience Certificate') }}
@endsection

@section('content')
@php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::GetLogo();
    $settings = \App\Models\Utility::settings();
    $date = date('Y-m-d');
    
    // Calculate duration
    $date1 = date_create($employees->company_doj);
    $date2 = date_create($employees->termination_date ?? date('Y-m-d'));
    $diff = date_diff($date1, $date2);
    $duration = $diff->format("%y years, %m months, %d days");
    
    // Get resignation details if exists
    $resignation = \App\Models\Resignation::where('employee_id', $employees->id)
        ->where('created_by', \Auth::user()->creatorId())
        ->first();
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card mt-5" id="printTable">
            <div class="card-body p-5" id="boxes" style="background: white;">
                
                {{-- Single Page Experience Certificate --}}
                <div style="min-height: 900px;">
                    {{-- Header with Logo --}}
                    <div class="letter-header mb-4">
                        <div class="d-flex justify-content-start align-items-start">
                            <div>
                                @if($company_logo)
                                    <img src="{{ $logo . '/' . $company_logo }}" 
                                         alt="Clearclaim" 
                                         style="max-height: 60px; max-width: 150px;">
                                @else
                                    <img src="{{ asset('storage/uploads/logo/logo.svg') }}" 
                                         alt="Clearclaim" 
                                         style="max-height: 60px; max-width: 150px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="employee-details mb-4" style="font-size: 12px;">
                        <div>{{ \Auth::user()->dateFormat($date) }}</div>
                    </div>

                    {{-- Subject Line --}}
                    <div class="subject-line mb-4" style="font-size: 14px; font-weight: bold; text-align: center;">
                        <div>EXPERIENCE CERTIFICATE</div>
                    </div>

                    {{-- Letter Body --}}
                    <div class="letter-body" style="font-size: 12px; line-height: 1.4; text-align: justify;">
                        <p><strong>To Whomsoever It May Concern</strong></p>
                        
                        <p>This is to certify that <strong>{{ $employees->name }}</strong> has worked with <strong>Clearclaim Ventures Private Limited</strong> from <strong>{{ $employees->company_doj ? \Auth::user()->dateFormat($employees->company_doj) : '[Start Date]' }}</strong> to <strong>{{ $employees->termination_date ? \Auth::user()->dateFormat($employees->termination_date) : ($resignation ? \Auth::user()->dateFormat($resignation->resignation_date) : '[End Date]') }}</strong>.</p>
                        
                        <p>During their tenure, <strong>{{ $employees->name }}</strong> was working in the <strong>{{ $employees->department->name ?? '[Department]' }}</strong> department as <strong>{{ $employees->designation->name ?? '[Designation]' }}</strong>.</p>
                        
                        <p>Throughout their employment period of <strong>{{ $duration }}</strong>, they demonstrated excellent professional skills and contributed significantly to the organization's goals. Their performance was consistently good and they maintained a positive attitude towards their work and colleagues.</p>
                        
                        <p>We found <strong>{{ $employees->name }}</strong> to be a dedicated and responsible professional who handled their assigned duties efficiently. They possess good technical knowledge and interpersonal skills.</p>
                        
                        <p>We wish <strong>{{ $employees->name }}</strong> the very best in their future endeavors and are confident that they will be a valuable asset to any organization they choose to join.</p>
                        
                        <p style="margin-top: 30px;">For <strong>Clearclaim Ventures Pvt. Ltd.</strong></p><br>
                        
                        {{-- Signature Block --}}
                        <div style="margin-top: 20px; display: flex; justify-content: flex-start; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('storage/letter/sign.png') }}" alt="Signature" style="max-height: 60px; max-width: 150px;">
                                    <div style="border-top: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong>Shrikant Pandore</strong><br>
                                    CEO<br>
                                    Clearclaim Ventures Private Limited
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-start; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('storage/letter/stamp.jpg') }}" alt="Stamp" style="max-height: 80px; max-width: 80px;">
                                </div>
                            </div>
                        </div>

                        {{-- Footer with Company Info --}}
                        <div style="margin-top: 50px; font-size: 11px; text-align: center;">
                            <div><strong>Clearclaim Ventures Private Limited</strong></div>
                            <div>Office No 201, 2nd floor, Vantage Tower, NDA Pashan Road, Bavdhan Pune 411021, India.</div>
                            <div>Phone No. 9156701900 | Website: www.clearclaim.in | Email: hr@clearclaim.in</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .page-break {
            page-break-after: always;
            page-break-inside: avoid;
        }
    }

    .letter-header {
        margin-bottom: 20px;
    }

    .letter-body {
        font-family: 'Times New Roman', serif;
        color: #000;
    }

    .letter-body p {
        margin-bottom: 10px;
    }

    .letter-body strong {
        font-weight: bold;
    }

    #printTable {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .card-body {
        background: white;
    }
</style>
@endpush
