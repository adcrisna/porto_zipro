<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Travel Insurance</title>
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

    <link href="{{ asset('assets/travel/css/material-kit.css?v=2.2.0') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/demo.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/vertical-nav.css') }}" rel="stylesheet" />
    @include('travel.styles')

</head>

<body class="landing-page" style="background-color:#ffffff">
    <div class="main">
        <div class="container ">
            <div class="section">
                <div class="form__penutupan">
                    <div class="inputPenutupan" style="">
                        <form class="formDataPenutupan" novalidate>
                            <div class="counter">
                                <h4 class="title color-info-1">
                                    Pemegang Polis
                                </h4>
                                <div class="mt-3">
                                    <select class="selectpicker w-100 text-dark pb-5 bg-section type-1" data-live-search="true"
                                        data-style="select-with-transition" name="penutupan[0][insured_title]"
                                        title="TITLE" required>
                                        <option value="nyonya">Nyonya</option>
                                        <option value="tuan">Tuan</option>
                                        <option value="nona">Nona</option>
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_firstname]"
                                        value="" placeholder="NAMA DEPAN">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_lastname]"
                                        value="" placeholder="NAMA BELAKANG">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_birthplace]"
                                        value="" placeholder="TEMPAT LAHIR">
                                </div>
                                <div class="mt-3">
                                    <div class="input-group has-primary">
                                        <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="insuredbirth-0"
                                            value="" placeholder="TANGGAL LAHIR" name="penutupan[0][insured_dob]">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <select class="selectpicker w-100 text-dark pb-5 bg-section type-1" data-live-search="true"
                                        data-style="select-with-transition" name="penutupan[0][insured_identity]"
                                        title="PILIHAN IDENTITAS" required>
                                        <option value="KTP">KTP</option>
                                        <option value="Passport">PASPOR</option>
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_noindentity]"
                                        value="" placeholder="NO IDENTITAS">
                                </div>
                                <div class="mt-3">
                                    <input type="number" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_phone]"
                                        value="" placeholder="NO HANDPHONE">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_email]"
                                        value="" placeholder="EMAIL">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_origin]"
                                        value="" placeholder="KOTA TEMPAT TINGGAL">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_poscode]"
                                        value="" placeholder="KODE POS">
                                </div>
                                <div class="mt-3">
                                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[0][insured_alamat]"
                                        value="" placeholder="ALAMAT TEMPAT TINGGAL">
                                </div>
                            </div>
                            @if($data['product_type'] !== "Individual")
                                <div class="counter form-anak-pasangan">
                                    <h4 class="title mt-4">
                                        Data Pasangan/Anak
                                    </h4>
                                    <div class="mt-3">
                                        <select class="selectpicker w-100 text-dark pb-5 bg-section type-1" data-live-search="true"
                                            data-style="select-with-transition" name="penutupan[1][insured_title]"
                                            title="TITLE" required>
                                            <option value="nyonya">Nyonya</option>
                                            <option value="tuan">Tuan</option>
                                            <option value="nona">Nona</option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[1][insured_firstname]"
                                            value="" placeholder="NAMA DEPAN">
                                    </div>
                                    <div class="mt-3">
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[1][insured_lastname]"
                                            value="" placeholder="NAMA BELAKANG">
                                    </div>
                                    <div class="mt-3">
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[1][insured_birthplace]"
                                            value="" placeholder="TEMPAT LAHIR">
                                    </div>
                                    <div class="mt-3">
                                        <div class="input-group has-primary">
                                            <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="insuredbirth-1"
                                                value="" placeholder="TANGGAL LAHIR" name="penutupan[1][insured_dob]">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <select class="selectpicker w-100 text-dark pb-5 bg-section type-1" data-live-search="true"
                                            data-style="select-with-transition" name="penutupan[1][insured_identity]"
                                            title="PILIHAN IDENTITAS" required>
                                            <option value="KTP">KTP</option>
                                            <option value="Passport">PASPOR</option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[1][insured_noindentity]"
                                            value="" placeholder="NO IDENTITAS">
                                    </div>
                                    <div class="mt-3">
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[1][insured_alamat]"
                                            value="" placeholder="ALAMAT TEMPAT TINGGAL">
                                    </div>
                                    <div class="mt-3">
                                        <select class="selectpicker w-100 text-dark pb-5 bg-section type-1" data-live-search="true"
                                            data-style="select-with-transition" name="penutupan[1][insured_relationship]"
                                            title="HUBUNGAN DENGAN PEMEGANG POLIS" required>
                                            @if($data['product_type'] !== "Duo Plus")
                                                <option value="Spouse">Pasangan</option>
                                            @endif
                                            <option value="Child">Anak</option>
                                            <option value="Family">Keluarga</option>
                                            <option value="Friend">Teman</option>
                                            <option value="Other">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-form-penutupan"></div>
                                @if($data['product_type'] !== "Duo Plus")
                                    <div class="sub-judul bg-section mb-1 shadow-none mt-4 w-75 form-anak-pasangan">
                                        <div class="d-flex  font-weight-normal pl-2 text-left">
                                            <button class="btn btn-info-2 btn-sm btn-add-family" type="button">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <p class="mt-2 ml-4">TAMBAH DATA ANAK</p>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="mt-5">
                                <div class="col-sm-12">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" value="checked" name="tnc">
                                                {{-- <a href="javascript:;" data-toggle="modal" data-target="#modalTnC">
                                                    I agree to terms and condition
                                                </a> --}}
                                                <a href="javascript:;" class="tnc-downloader">
                                                    I agree to terms and condition
                                                </a>
                                            <span class="form-check-sign">
                                                <span class="check"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5">
                                <div class="col-sm-12 text-center">
                                    <button class="btn btn-info-2 btn-round bg-primary-2 btn-complete btn-block" type="button">KIRIM</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--   Core JS Files   -->
    <script src="{{ asset('assets/travel/js/core/jquery.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/core/popper.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/core/bootstrap-material-design.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/plugins/moment.min.js ')}}"></script>
    <script src="{{ asset('assets/travel/js/plugins/bootstrap-datetimepicker.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/plugins/nouislider.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/plugins/bootstrap-tagsinput.js ')}}"></script>
    <script src="{{ asset('assets/travel/js/plugins/bootstrap-selectpicker.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/plugins/jasny-bootstrap.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/plugins/jquery.flexisel.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/demo/modernizr.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/demo/vertical-nav.js ')}}" type="text/javascript"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="{{ asset('assets/travel/js/material-kit.js?v=2.2.0 ')}}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @include('travel.penutupan.script')

</body>

</html>