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
    
    // Set pronouns based on gender
    $gender = strtolower($employees->gender ?? 'male');
    if ($gender === 'female') {
        $subject_pronoun = 'She';
        $object_pronoun = 'her';
        $possessive_pronoun = 'Her';
    } else {
        $subject_pronoun = 'He';
        $object_pronoun = 'him';
        $possessive_pronoun = 'His';
    }
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

                    {{-- Subject Line --}}
                    <div class="subject-line mb-4" style="font-size: 14px; font-weight: bold; text-align: center;">
                        <div>EXPERIENCE CERTIFICATE</div>
                    </div>

                    
                    {{-- Date --}}
                    <div class="employee-details mb-4" style="font-size: 12px;">
                        <div style="text-align: right;">{{ \Auth::user()->dateFormat($date) }}</div>
                    </div>

                    {{-- Letter Body --}}
                    <div class="letter-body" style="font-size: 12px; line-height: 1.4; text-align: justify;">
                        <p style="text-align: center;"> <strong>To Whomsoever It May Concern</strong></p>
                        
                        <p>This letter is to certify that <strong>{{ $employees->name }}</strong> has successfully completed {{ strtolower($possessive_pronoun) }} internship program with Clearclaim Ventures Private Limited. {{ $possessive_pronoun }} internship period was from <strong>{{ $employees->company_doj ? \Auth::user()->dateFormat($employees->company_doj) : '[Start Date]' }}</strong> to <strong>{{ $employees->termination_date ? \Auth::user()->dateFormat($employees->termination_date) : ($resignation ? \Auth::user()->dateFormat($resignation->resignation_date) : '[End Date]') }}</strong>. {{ $subject_pronoun }} was working with '<strong>{{ $employees->department->name ?? '[Department]' }}</strong>' department as '<strong>{{ $employees->designation->name ?? '[Designation]' }}</strong>' and was actively involved in the projects and tasks assigned to {{ $object_pronoun }}.</p>
                        
                        <p>During the span, we found {{ $object_pronoun }} punctual and reliable person. {{ $possessive_pronoun }} learning powers are good and {{ strtolower($subject_pronoun) }} picks up quickly. {{ $possessive_pronoun }} feedback and evaluation proved that {{ strtolower($subject_pronoun) }} learned strongly. Moreover, {{ strtolower($possessive_pronoun) }} interpersonal and communication skills are brilliant. We found {{ $object_pronoun }} exceptional in {{ strtolower($possessive_pronoun) }} performance.</p>
                        
                        <p>We wish {{ $object_pronoun }} a bright future.</p>
                        
                        <p style="margin-top: 30px;">Sincerely,</p><br>
                        
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

@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function closeScript() {
        setTimeout(function () {
            window.open(window.location, '_self').close();
        }, 1000);
    }

    $(window).on('load', function () {
        var element = document.getElementById('boxes');
        var opt = {
            filename: '{{ $employees->name }}_Experience_Certificate',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {
                scale: 4, 
                dpi: 72, 
                letterRendering: true,
                useCORS: true
            },
            jsPDF: {
                unit: 'in', 
                format: 'A4',
                orientation: 'portrait'
            },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(closeScript);
    });
</script>
@endpush
