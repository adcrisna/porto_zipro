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
    <style>
        .page-header {
            height: 8vh;
        }

        .header-filter:before {
            background-color: rgb(218, 219, 219)
        }

        .form-check .form-check-label .circle .check {
            background-color: #0d59fc;
        }

        .form-check .form-check-input:checked~.circle {
            border-color: rgb(118, 118, 118);
        }

        .form-check.form-check-radio>.form-check-label {
            color: black !important;
        }

        .form-check .form-check-input:checked~.form-check-sign .check {
            background: #0d59fc;
        }

        .media .avatar {
            width: 160px;
            height: 159px;
            padding: 15px;
        }
    </style>

</head>

<body class="landing-page" style="background-color: rgb(218, 219, 219)">
    <div class="page-header header-filter" data-parallax="true">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="title"></h1>
                </div>
            </div>
        </div>
    </div>
    <div class="main main-raised">
        <div class="container">
            <div class="section">
                <div class="row">
                    <div class="col-md-8 ml-auto mr-auto">
                        <h2 class="title text-center" style="color: rgb(35,54,111);">Travel Insurance</h2>
                    </div>
                </div>
                <div class="features mt-5">
                    <form class="formAction" onsubmit="event.preventDefault(); initSubmit(this)">
                        <input type="hidden" value="{{ $product_id }}" name="salvus_product_id">
                        <div class="title">
                            <h4 class="font-weight-bold">Is there any Traveler aged 70 years old and over?</h4>
                        </div>
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="age" id="ageYes" value="yes">
                                Yes
                                <span class="circle">
                                    <span class="check"></span>
                                </span>
                            </label>
                        </div>
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="age" id="ageNo" value="no">
                                No
                                <span class="circle">
                                    <span class="check"></span>
                                </span>
                            </label>
                        </div>
                        <div id="formAlreadyTrav" style="display: none;">
                            <div class="title">
                                <h4 class="font-weight-bold">Already Traveling?</h4>
                            </div>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravYes"
                                        value="yes">
                                    Yes
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravNo"
                                        value="no">
                                    No
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        {{-- Already Traveling Date --}}
                        <div class="alreadytravel-date mt-5" style="display: none;">
                            <div class="title mb-1">
                                <h4>Depature Date</h4>
                            </div>
                            <div class="form-group pt-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datepicker" name="depature_date" id="inputdepartureDate"
                                        placeholder="Depature Date" />
                                </div>
                            </div>

                            <div class="card card-disclaim" style="display: none;">
                                <div class="card-body">
                                    <h4 class="card-title"><i class="fa fa-warning text-info"></i> Disclaimer :</h4>
                                    <p class="card-text">
                                        Anda diperbolehkan membeli polis ketika Anda sudah berada di Luar Negeri, dengan
                                        ketentuan bahwa pembelian polis tidak
                                        boleh lebih dari 3 (tiga) hari sejak Anda meninggalkan Indonesia.
                                        <br><br>
                                        Sejak tanggal polis diterbitkan, terdapat Masa Tunggu 72 (tujuh puluh dua) jam
                                        sebelum perlindungan asuransi ini mulai
                                        berlaku.
                                        <br><br>
                                        Setiap Penyakit atau Cedera yang terjadi dalam waktu masa tunggu akan dianggap
                                        sebagai Kondisi Medis Yang Sudah Ada
                                        sebelumnya dan setiap kerugian, kerusakan atau tanggung jawab yang timbul dalam
                                        waktu Masa Tunggu tidak dijamin dalam
                                        Polis ini.
                                    </p>

                                    <div class="form-check mt-5">
                                        <label class="form-check-label pl-5 text-dark">
                                            <input class="form-check-input disclaimer" type="checkbox" name="disclaimer"
                                                id="disclaimer" value="">
                                            I agree with term and conditions
                                            <span class="form-check-sign">
                                                <span class="check"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Input Product Search --}}
                        <div class="formDestination mt-5" style="display:none;">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="startDate" class="text-dark">Start Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datepicker col-sm-6" id="startDate"
                                            value="{{ date('d M Y') }}" placeholder="{{ date('d M Y') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label for="endDate">End Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control datepicker endDate col-sm-6" id="endDate"
                                            value="{{ date('d M Y') }}" placeholder="{{ date('d M Y') }}" required />
                                    </div>
                                </div>
                                <div class="col-sm-4 px-3">
                                    <label for="days">Day(s)</label>
                                    <input type="number" class="form-control col-sm-3 days" name="days" min="1" id="days"
                                        value="1" required>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-sm-6">
                                    <select class="selectpicker w-100 desArea" data-live-search="true"
                                        data-style="select-with-transition" name="destinationArea"
                                        title="Destination Area" required>
                                        @foreach($regions['Regions' ] as $key => $value)
                                            <option value="{{ $value['ID'] }}">{{ $value['Name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6 divcountry text-center">
                                    <select class="selectpicker w-100 desCountry" data-live-search="true"
                                        data-style="select-with-transition" name="destinationCountry"
                                        title="Destination Country" data-size="7">
                                        <option>Please select Destination Area first</option>
                                    </select>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="selectpicker w-100 typePlan" data-live-search="true"
                                        data-style="select-with-transition" name="package_type" title="Package Types"
                                        data-size="7" required>
                                        @foreach ($types as $type)
                                            <option value="{{ $type['ID'] }}">{{ $type['Name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="selectpicker w-100 travelneeds" data-live-search="true"
                                        data-style="select-with-transition" name="travel_need" title="Travel Needs"
                                        data-size="7" required>
                                        @foreach ($travelneeds as $travelneed)
                                            <option value="{{ $travelneed['ID'] }}">{{ $travelneed['Name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <select class="selectpicker w-100 origins" data-live-search="true"
                                        data-style="select-with-transition" name="origins" title="Origins"
                                        data-size="7" required>
                                        @foreach ($origins as $origin)
                                            <option value="{{ $origin['ID'] }}">{{ $origin['Name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col-sm-12 text-center">
                                    <button class="btn btn-info btn-submit-section" type="button">
                                        submit
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="productResult mt-5 pt-4"></div>
                    </form>
                    <div class="policyHolder mt-5" style="display:none;">
                        <div class="section">
                            <div class="features ">
                                <form id="TravellersInfo" onsubmit="submitTraveller(this)">
                                    @for($ie = 0; $ie < 10; $ie++)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-nav-tabs">
                                                    <div class="card-header card-header-info">
                                                        <div class="nav-tabs-navigation">
                                                            <div class="nav-tabs-wrapper">
                                                                <ul class="nav nav-tabs" data-tabs="tabs">
                                                                    <li class="nav-item">
                                                                        @php
                                                                            if($ie == 0) {
                                                                                $title = "Insured (Policy Holder)";
                                                                            }elseif($ie == 1) {
                                                                                $title = "Traveler <span id='idtitle'></span>";
                                                                            }elseif($ie > 1) {
                                                                                $title = "Traveler (Child)";
                                                                            }
                                                                        @endphp
                                                                        <a class="nav-link disabled" href="#single"
                                                                            data-toggle="tab">
                                                                            <span class="badge badge-pills badge-success mr-3">{{ $ie + 1 }}</span> {!! $title !!}
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-5">
                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                <div class="form-group">
                                                                    <label for="title_insurer">Title</label>
                                                                    <select class="form-control selectpicker" name="title[{{$ie}}]" data-style="btn btn-link" id="title_insurer">
                                                                        <option value="mr" {{$ie == 0 ? "selected" : ""}}>MR</option>
                                                                        <option value="ms">MS</option>
                                                                        <option value="mrs" {{$ie == 1 ? "selected" : ""}}>MRS</option>
                                                                        <option value="mstr" {{$ie == 2 ? "selected" : ""}}>MSTR</option>
                                                                        <option value="miss">MISS</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-10">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="firstName">First Name <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control mt-3" name="first_name[{{$ie}}]" id="firstName" placeholder="First Name">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="lastName">Last Name</label>
                                                                            <input type="text" class="form-control mt-3" id="lastName" name="last_name[{{$ie}}]" placeholder="Last Name">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="plaecofBirth">Place of Birth</label>
                                                                            <input type="text" class="form-control mt-3" id="plaecofBirth" name="place_birth[{{$ie}}]" placeholder="Place of Birth">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="dateofbirth">Date of Birth <span class="text-danger">*</span></label>
                                                                        <div class="row">
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <select class="form-control selectpicker" name="date_birth[{{$ie}}]" data-style="btn btn-link" id="dateofbirth">
                                                                                        <option selected disabled></option>
                                                                                        @for($i = 1; $i < 32; $i++)
                                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                                        @endfor
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <select class="form-control selectpicker" name="month_birth[{{$ie}}]" data-style="btn btn-link">
                                                                                        <option selected disabled></option>
                                                                                        @for($i = 1; $i < 13; $i++)
                                                                                            <option value="{{ $i }}">{{ date('M', mktime(0,0,0,$i, 1, date('Y'))) }}</option>
                                                                                        @endfor
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <select class="form-control selectpicker" name="year_birth[{{$ie}}]" data-style="btn btn-link">
                                                                                        <option selected disabled></option>
                                                                                        @for($i = date('Y'); $i > (date('Y') - 71); $i--)
                                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                                        @endfor
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <label for="idNumber">ID Number <span class="text-danger">*</span></label>
                                                                        <div class="row">
                                                                            <div class="col-md-6 col-5">
                                                                                <div class="form-group">
                                                                                    <select class="form-control selectpicker mt-3" name="identity[{{$ie}}]" data-style="btn btn-link" id="idNumber">
                                                                                        <option value="KTP">KTP/NIK</option>
                                                                                        <option value="KITAS">KITAS</option>
                                                                                        <option value="Passport">PASSPORT</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-7">
                                                                                <div class="form-group">
                                                                                    <input type="text" class="form-control" name="id_number[{{$ie}}]" id="id_number" placeholder="ID Number">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <span class="small text-muted">
                                                                                    *If the passenger does not have a NIK, please fill the ID number with 0000
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @if($ie == 0)
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                                                                <input type="number" class="form-control mt-4" name="phone_number" id="phone" placeholder="Mobile Phone">
                                                                            </div>
                                                                        </div>
                                                                    @elseif($ie == 1)
                                                                        <div class="col-md-6">
                                                                            <label class="">Relationship with policy holder <span class="text-danger">*</span></label>
                                                                            <div class="form-check form-check-radio">
                                                                                <label class="form-check-label text-muted">
                                                                                    <input class="form-check-input relationships" type="radio" name="relationships" id="relationships" value="Spouse">
                                                                                    Spouse
                                                                                    <span class="circle">
                                                                                        <span class="check"></span>
                                                                                    </span>
                                                                                </label>
                                                                            </div>
                                                                            <div class="form-check form-check-radio">
                                                                                <label class="form-check-label">
                                                                                    <input class="form-check-input relationships" type="radio" name="relationships" id="relationships" value="Child">
                                                                                    Child
                                                                                    <span class="circle">
                                                                                        <span class="check"></span>
                                                                                    </span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @if($ie == 0)
                                                                    <div class="row mt-3">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                                                <input type="email" class="form-control mt-4" name="email" id="email" placeholder="name@example.com">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="additional_email">Additional Email</label>
                                                                                <input type="email" class="form-control mt-4" name="additional_email" id="additional_email" placeholder="name@example.com">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="city">City <span class="text-danger">*</span></label>
                                                                            <select class="selectpicker w-100 cities mt-3" data-live-search="true"
                                                                                data-style="select-with-transition" data-container="false" name="cities" title="Select an option"
                                                                                data-size="7" required>
                                                                                @foreach ($cities as $city)
                                                                                    <option value="{{ $city['Name'] }}">{{ $city['Name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="postal_code">Postal Code</label>
                                                                            <input type="text" class="form-control mt-4" id="postal_code" placeholder="Postal Code">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="address">Address <span class="text-danger">*</span> </label>
                                                                            <textarea class="form-control" id="address" name="address[{{$ie}}]" rows="3"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                    <button type="button" class="btn btn-success btn-block" id="submitTraveller">Submit</button>
                                </form>
                            </div>
                        </div>
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

    <script>
        $("input[name=age]").on('change', function () {
            $('#formAlreadyTrav').show();
        });

        $('#startDate').datetimepicker({
            format: 'DD MMM YYYY',
            date: new Date(),
            minDate: new Date().setDate(new Date().getDate() - 1),
            disabledDates: [
                new Date().setDate(new Date().getDate() - 1)
            ],
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });
        $('#endDate').datetimepicker({
            format: 'DD MMM YYYY',
            date: new Date(),
            minDate: new Date(),
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });

        // INIT DATE 
        const datenow = new Date();
        let minDate = datenow.setDate(datenow.getDate() - 4);
        $('#inputdepartureDate').datetimepicker({
            format: 'DD MMM YYYY',
            date: new Date(),
            maxDate: new Date(),
            minDate: minDate,
            disabledDates: [
                new Date().setDate(new Date().getDate() - 4)
            ],
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            }
        });
        $("input[name=alreadyTrav]").on('change', function (e) {
            if ($(this).val() == 'yes') {
                $(".alreadytravel-date").show();
                $(".formDestination").hide();
                $("#startDate").prop("disabled", true);
            } else {
                $(".alreadytravel-date").hide();
                $(".formDestination").show();
                $("#startDate").prop("disabled", false);
            }
        });

        $("#disclaimer").change(function () {

            if ($(this).prop('checked')) {
                $(".formDestination").show();
                $("#startDate").prop("disabled", true);
            } else {
                $(".formDestination").hide();
            }
        });

        $('#inputdepartureDate').on('dp.change', function (e) {
            $('.card-disclaim').show();
            var depatureDate = new Date(e.date);
            $('#startDate').data('DateTimePicker').date(depatureDate)
        });

        $('#startDate').on('dp.change', function (e) {
            e.preventDefault()
            var endDate = $('#endDate').data('DateTimePicker');
            var startDate = new Date(e.date);
            var dateEnd = new Date(endDate.date());
            if (startDate.getDate() > dateEnd.getDate()) {
                endDate.date(startDate)
            }
            endDate.minDate(e.date);
            let days = getDatesInRange(startDate, dateEnd);
            if(days == 0) {
                days = 1;
            }
            $("#days").val(days)
        });

        $('#endDate').on('dp.change', function (e) {
            // e.preventDefault()
            const start = $('#startDate').data('DateTimePicker').date();
            let endDate = $('#endDate').data('DateTimePicker')
            var date = new Date(e.date);
            let startDate = new Date(start)
            endDate.date(date)
            let days = getDatesInRange(startDate, date);
            if(days == 0) {
                days = 1;
            }
            $("#days").val(days)
        });

        function getDatesInRange(startDate, endDate) {
            const date = new Date(startDate.getTime());

            // const dates = [];
            var counter = 0;

            while (date <= endDate) {
                // dates.push(new Date(date));
                counter = counter + 1;
                date.setDate(date.getDate() + 1);
            }

            // return dates;
            return counter;
        }
    </script>
    <script>
        $(".days").on('change', function (e, state) {
            if(e.originalEvent === undefined){
                return
            };

            let value = $(this).val() - 1;
            if(value == 0) {
                value = 1;
            }
            const start_date = new Date();
            let changeDate = moment(start_date, "DD MMM YYYY").add('days', value);
            $('#endDate').data('DateTimePicker').date(changeDate)
        })
    </script>

    <script>
        $('.desArea').on('change', function (e) {
            $('.desCountry').remove();
            let val = $(this).find(":selected").val()
            $('.divcountry').append('<i class="fa fa-spinner fa-4x fa-spin  text-info"></i>')
            $('.divcountry').append(
                `<select class="selectpicker w-100 desCountry" data-live-search="true" data-style="select-with-transition" name="destinationCountry" title="Destination Country" data-size="7" required></select>`
            )
            $.ajax({
                type: "GET",
                data: {
                    area_id: val
                },
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('travel.getcountry') }}" + "?area_id=" + val,
                success: function (res) {
                    res['country'].forEach((v, k) => {
                        $('.divcountry').find('i').remove();
                        $('select.desCountry').append(`<option value="` + v.ID + `">` + v
                            .Name + `</option>`)
                        $('select.desCountry').selectpicker();
                    });
                }
            });
        });
    </script>
    <script>
        $(function() {
            $(".relationships").on('change', function() {
                if( $(this).is(':checked') ) {
                    $("#idtitle").text("("+$(this).val()+")");
                    console.log($(this).val());
                }
            });
        })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(".btn-submit-section").click(function() {
            return initSubmit($('.formAction'));
        })

        function initSubmit(form) {
            let data = $(form).serializeArray()
            let start_date = {
                name: "start_date",
                value: new Date($("#startDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }
            let end_date = {
                name: "end_date",
                value: new Date($("#endDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }

            data.push(start_date);
            data.push(end_date);
            let newdata = {};
            for (i in data) {
                newdata[data[i].name] = data[i].value
            }
            $('.productResult').empty();
            $('.productResult').addClass('text-center');
            $('.productResult').append(`<i class="fa fa-spinner fa-5x fa-spin text-info"></i>`);
            $.ajax({
                type: "POST",
                data: {
                    data: newdata
                },
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('travel.getProduct') }}",
                success: function (res) {
                    $('.productResult').removeClass('text-center');
                    $('.productResult').empty();
                    $('.productResult').append(res);
                    $('.nav-typeOfPlan').text($('.typePlan option:selected').text())

                    $('.nav-select-product').click(function() {
                        $('.nav-select-product').removeClass('btn-success').addClass('btn-info');
                        $('.card-select').removeClass('card-header-success').addClass('card-header-info');
                        $('.card-title-bar').text('');
                        let key = $(this).attr('data-keys');
                        let product_id = $(this).attr('data-ids');
                        let plan_id = $(this).attr('data-planIds');
                        $(".card-select-" + key).removeClass('card-header-info').addClass('card-header-success');
                        $('.card-title-select-' + key).text('SELECTED');
                        $(this).removeClass('btn-info').addClass('btn-success');
                        $("#zurich_product_id").val(product_id);
                        $("#zurich_plan_id").val(plan_id);

                        $('.policyHolder').show();
                        $(function() {
                            $(".relationships").on('change', function() {
                                if( $(this).is(':checked') ) {
                                    $("#idtitle").text("("+$(this).val()+")");
                                    console.log($(this).val());
                                }
                            });
                        })
                    });
                },
                error: function (err, textStatus, errorThrown) {
                    $('.productResult').empty();
                    let res = JSON.parse(err.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: res.message
                    })
                }
            });
        }
    </script>
    <script>
        $(function() {
            $("#submitTraveller").click(function() {
                let data = $("#TravellersInfo").serializeArray();
                let action = $(".formAction").serializeArray();

                let start_date = {
                    name: "start_date",
                    value: new Date($("#startDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
                }
                let end_date = {
                    name: "end_date",
                    value: new Date($("#endDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
                }

                let depatureDate = {
                    name: "depature_date",
                    value: new Date($("#inputdepartureDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
                }


                action.push(start_date);
                action.push(end_date);
                action.push(depatureDate);

                var obj = {};

                for (i in action) {
                    obj[action[i].name] = action[i].value
                }
                
                for (index in data) {
                    obj[data[index].name] = data[index].value;
                };


                $.ajax({
                    type: "POST",
                    data: obj,
                    headers: {
                        'Authorization': 'Bearer '+"{{ $token }}"
                    },
                    url: "{{ route('travel.info') }}",
                    success: function(res) {
                        if(res.status == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Order telah berhasil, silahkan lakukan pembayaran.'
                            });
                            callback.postMessage('/');
                        }
                    },
                    error: function (err, textStatus, errorThrown) {
                        let res = JSON.parse(err.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: res.message
                        })
                    }
                })
            })
        });
    </script>

</body>

</html>