@extends('layouts.contractheader')

@section('page-title')
    {{ __('Increment Letter') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="container">
            <div class="card mt-5" id="printTable" style="margin-left: 180px;margin-right: -57px; padding: 20px;">
                <div class="card-body" id="boxes">
                    <div style="padding: 50px;">
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
                        

                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <div>
                                {{ \Carbon\Carbon::parse($increment->created_at)->format('d/m/Y') }}<br>
                                {{ $employee->name }}<br>
                                {{ $employee->designation->name ?? '' }}<br>
                                {{ $employee->department->name ?? '' }}</p>
                            </div>
                        </div>

                        <h2 class="text-center">Increment Letter</h2>


                        <p>Dear {{ explode(' ', $employee->name)[0] }},</p>

                        <p>
                            We are pleased to inform you that in recognition of your continued dedication and performance, 
                            your salary has been revised effective from <strong>{{ $increment->month_of_effective_date ? \Carbon\Carbon::parse($increment->month_of_effective_date)->format('d/m/Y') : 'N/A' }}</strong>.
                        </p>

                        <p>
                            Your new compensation will be 
                            <strong>{{ \Auth::user()->priceFormat($increment->new_salary) }}</strong> per annum, 
                            an increment of <strong>{{ \Auth::user()->priceFormat($increment->increment_amount) }}</strong> 
                            from your previous salary of <strong>{{ \Auth::user()->priceFormat($increment->old_salary) }}</strong>. 
                            This increment reflects our appreciation of your contributions and our confidence in your continued success with us.
                        </p>

                        <p>
                            Please note that this change will be reflected in your salary from 
                            <strong>{{ $increment->month_of_effective_date ? \Carbon\Carbon::parse($increment->month_of_effective_date)->format('d/m/Y') : 'N/A' }}</strong> onwards.
                        </p>

                        <p>
                            We value your commitment to the organization and look forward to your continued contributions.
                        </p>

                        <br><br>

                        {{-- Signature Block --}}
                        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('images/letter/sign.png') }}" alt="Sign" style="max-height: 80px; max-width: 80px;">
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
                                    <div><strong>{{ $employee->name }}</strong></div>
                                    <div>Accepted and Acknowledged</div>
                                    <div>Must be signed on {{ $employee->company_doj ? \Auth::user()->dateFormat($employee->company_doj) : __('Not Set') }}</div>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div style="width: 45%; text-align: left;">
                                <div style="margin-top: 20px;">
                                    <img src="{{ asset('images/letter/stamp.jpg') }}" alt="Stamp" style="max-height: 80px; max-width: 80px;">
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

@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function closeScript() {
        setTimeout(function () {
            window.location.href = '/setsalary';
        }, 1000);
    }

    $(window).on('load', function () {
        var element = document.getElementById('boxes');
        var opt = {
            filename: 'Increment_Letter_{{ $employee->name }}',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: 'in', format: 'A4' }
        };

        html2pdf().set(opt).from(element).save().then(closeScript);
    });
</script>
@endpush
