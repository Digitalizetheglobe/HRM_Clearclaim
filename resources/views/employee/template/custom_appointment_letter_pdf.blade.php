@extends('layouts.contractheader')
@section('page-title')
    {{ __('Appointment Letter') }}
@endsection

@section('content')
@php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::GetLogo();
    $settings = \App\Models\Utility::settings();
    $date = isset($appointmentDate) ? $appointmentDate : date('Y-m-d');
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card mt-5" id="printTable">
            <div class="card-body p-5" id="boxes" style="background: white;">
                
                {{-- PAGE 1 --}}
                <div class="page-break" style="page-break-after: always; min-height: 900px;">
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

                    {{-- Date and Employee Details --}}
                    <div class="employee-details mb-4" style="font-size: 12px;">
                        <div>{{ \Auth::user()->dateFormat($date) }}</div>
                        <div><strong>{{ $employees->name }}</strong></div>
                        <div>{{ 'Pune' }}</div>
                    </div>

                    {{-- Subject Line --}}
                    <div class="subject-line mb-4" style="font-size: 14px; font-weight: bold; text-align: center;">
                        <div>APPOINTMENT LETTER</div>
                    </div>

                    {{-- Letter Body - Page 1 --}}
                    <div class="letter-body" style="font-size: 12px; line-height: 1.4; text-align: justify;">
                        <p>Dear <strong>{{ explode(' ', $employees->name)[0] }}</strong>,</p>
                        <p>With reference to your application and subsequent reviews, you had with us, we have pleasure in offering you employment. Your joining date will be <strong>{{ $employees->company_doj ? \Auth::user()->dateFormat($employees->company_doj) : '[Joining Date]' }}</strong> on the following terms and conditions:</p>

                        <p><strong>1. DESIGNATION / REPORTING RELATIONSHIP / GRADE</strong><br>
                        You will be designated as <strong>{{ $employees->designation->name ?? '[designation]' }}</strong><br>
                        You will be reporting to the Manager – <strong>{{ $employees->reportingManager->name ?? '[department]' }}</strong></p>

                        <p><strong>2. LOCATION</strong><br>
                        Bavdhan, Pune. However, the Company reserves the right to transfer you to any of its location in India, and further, reserves the right to transfer, assign, or depute your services to any of its group Companies at any location.</p>

                        <p><strong>3. MEDICAL FITNESS AND VERIFICATION OF PARTICULARS</strong><br>
                        Your Appointment is subject to:<br>
                        a) Verification of particulars mentioned in your application. In case those particulars are found unsatisfactory or false, your service is liable for termination without any reason or notice thereof at any time.<br>
                        b) You are being declared medically fit at the time of joining.</p>

                        <p><strong>4. REMUNERATION</strong><br>
                        From the date of joining, you will be entitled for annual CTC of Rs. <strong>{{ !empty($employees->salary) ? number_format($employees->salary, 2) : 'X.XX,XXX' }}</strong>/- (Rupees <strong>{{ !empty($employees->salary) ? 'Amount in Words' : 'XX-Lakhs-XXXX-Thousand-XXX-Hundred Only' }}</strong>) per annum. The information relating to your pay and other perquisites etc. will be a matter of confidence between you and the company and shall not be divulged to anyone.<br>
                        You are also entitled for the uncapped monthly sales incentives as per latest incentive structure applicable apart from the salary.</p>

                        <p><strong>5. PROBATION</strong><br>
                        You will be on probation for a period of three (3) months from the date of joining. At the end of the probationary period, based on the feedback received on your Confirmation Appraisal Review, your services shall be confirmed or extended to such period that the Company deems fit. Until you are informed in writing that you are confirmed, your services shall continue to remain on probation.</p>

                        <p><strong>6. NOTICE PERIOD</strong><br>
                        During the probation and on confirmation the services are terminable by either party by giving 75 day’s notice period.</p>

                        {{-- Footer with Company Info --}}
                        <div style="margin-top: 50px; font-size: 11px; text-align: center;">
                            <div><strong>Clearclaim Ventures Private Limited</strong></div>
                            <div>Office No 201, 2nd floor, Vantage Tower, NDA Pashan Road, Bavdhan Pune 411021, India. Phone No. 9156701900</div>
                            <div>Website: www.clearclaim.in Email: hr@clearclaim.in</div>
                        </div>
                    </div>
                </div>

                {{-- PAGE 2 --}}
                <div class="page-break" style="min-height: 900px; padding-top: 20px;">
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

                    <div class="letter-body" style="font-size: 12px; line-height: 1.4; text-align: justify;">
                        <p><strong>7. AGE OF RETIREMENT</strong><br>
                        During the probation and on confirmation the services are terminable by either party by giving 75 day’s notice period.</p>

                        <p><strong>8. RULES AND REGULATION</strong><br>
                        You will be governed by the Company's Rules and Regulations, which are in force or as will be in force from timeto time, other than those provided in this letter of Appointment.</p>

                        <p><strong>9. BAR ON DOUBLE EMPLOYMENT</strong><br>
                        You will be governed by the Company's Rules and Regulations, which are in force or as will be in force from timeto time, other than those provided in this letter of Appointment.</p>

                        <p><strong>10. SECRECY</strong><br>
                        You will keep secret and confidential, any and all knowledge and information regarding the products or activities of the company that you may obtain or come across in the course of your employment with the Company. Any contravention of these conditions shall entail termination of your services from the Company, or may even make you liable for prosecution for damages.</p>

                        <p><strong>11. ADDRESS</strong><br>
                        You will keep the Company informed of any change in your residential address.</p>

                        <p><strong>12. ADDRESS</strong><br>
                        All disputes of any kind including disputes regarding any claims or payments whether under this contract of employment or otherwise arising out of your employment, the jurisdiction will remain in Pune.</p>

                        <p>
                        Please return the duplicate copy of this letter duly signed by you as a taken of your acceptance, indicating your date of joining.</p>

                        <p style="margin-top: 20px;">For <strong>Clearclaim Ventures Pvt. Ltd.</strong></p><br>
                        {{-- Signature Block --}}
                        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('storage/letter/sign.png') }}" alt="Signature" style="max-height: 60px; max-width: 150px;">
                                    <div style="border-top: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong>Shrikant Pandore</strong><br>
                                    Authorized Signatory
                                </div>
                            </div>

                            <div style="width: 45%; text-align: right; margin-top: 20px;">
                                <div>
                                    <div style="border-top: 1px solid #000; width: 250px; margin-left: auto; margin-bottom: 5px;"></div>
                                    <div><strong>{{ $employees->name }}</strong></div>
                                    <div>Accepted and Acknowledged</div>
                                    <div>Must be signed on {{ $employees->company_doj ? \Auth::user()->dateFormat($employees->company_doj) : __('Not Set') }}</div>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('storage/letter/stamp.jpg') }}" alt="Stamp" style="max-height: 80px; max-width: 80px;">
                                </div>
                            </div>
                        </div>

                        {{-- Footer with Company Info --}}
                        <div style="margin-top: 50px; font-size: 11px; text-align: center;">
                            <div><strong>Clearclaim Ventures Private Limited</strong></div>
                            <div>Office No 201, 2nd floor, Vantage Tower, NDA Pashan Road, Bavdhan Pune 411021, India. Phone No. 9156701900</div>
                            <div>Website: www.clearclaim.in Email: hr@clearclaim.in</div>
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
            filename: '{{ $employees->name }}_Appointment_Letter',
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

