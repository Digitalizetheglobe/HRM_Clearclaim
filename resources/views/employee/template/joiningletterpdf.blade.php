@extends('layouts.contractheader')
@section('page-title')
    {{ __('Joining Letter') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card mt-5" id="printTable">
            <div class="card-body p-5 watermark-container" id="boxes">

                {{-- Header with logo and line --}}
                <div class="letter-header">
                    <div class="logo-section">
                        <img src="{{ asset('storage/uploads/logo/logo.svg') }}"
                             alt="{{ config('app.name', 'HRMGo') }}"
                             class="company-logo"
                             style="height:75px; float:left;">
                    </div>
                    <div class="header-line"></div>
                </div><br>

                {{-- Letter Content --}}
                <div class="letter-content mt-4">
                    <div class="letter-body">
                        <br><br><br>
                        <div class="formatted-content">
                            {!! $joiningletter->content !!}
                        </div>
                        <div class="closing mt-4"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Watermark container */
    .watermark-container {
        position: relative;
    }

    /* Background watermark */
    .watermark-container::before {
        content: "";
        background: url('{{ asset('storage/uploads/logo/logo.svg') }}') no-repeat center center;
        background-size: 300px 300px;   /* watermark size */
        opacity: 0.08;                  /* transparent watermark */
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        transform: translate(-50%, -50%);
        z-index: 0;
    }

    /* Foreground content above watermark */
    .watermark-container > * {
        position: relative;
        z-index: 1;
    }

    /* Logo top-left */
    .company-logo {
        height: 75px;
        display: block;
    }

    /* Half-width black line */
    .header-line {
        border-top: 2px solid #000;
        width: 50%;
        margin-top: 15px;
    }

    .letter-content {
        font-family: 'Times New Roman', serif;
        line-height: 1.6;
        margin-top: 25px;
    }

    #printTable {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .card-body {
        padding: 40px;
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
            filename: '{{$employees->name}}',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {scale: 4, dpi: 72, letterRendering: true},
            jsPDF: {unit: 'in', format: 'A4'}
        };

        html2pdf().set(opt).from(element).save().then(closeScript);
    });
</script>
@endpush
