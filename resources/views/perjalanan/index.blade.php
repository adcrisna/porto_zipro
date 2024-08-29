<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PA Perjalanan</title>
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

    <link href="{{ asset('assets/travel/css/material-kit.css?v=2.2.0') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/demo.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/vertical-nav.css') }}" rel="stylesheet" />
    <style>
        .page-header {
            height: 0vh;
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

        :root {
            --custom-primary-color: #1363B4;
        }
        .bg-section{
            background: #FAFAFA;
        }
        .btn-primary,
        .background-color,
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary.active,
        .dropdown-toggle.btn-primary {
            background-color: var(--custom-primary-color);
            border-color: var(--custom-primary-color);
        }

        .text-primary {
            color: var(--custom-primary-color);
        }

        .bg-primary-2 {
            background-color: #1363B4;
        }

        .bg-primary-3 {
            background-color: #0CBC8B;
        }

        .bg-primary-4 {
            background-color: #263570;
        }

        .text-custom-primary-color {
            color: var(--custom-primary-color);
        }

        .type-1 {
            border-radius:10px;
            border: 2px solid #eee;
            transition: .3s border-color;
            height: 45px;
        }
        .type-1:hover {
        border: 2px solid #aaa;
        border-radius:10px;
        }

        .type-2 {
            border-radius:10px;
            border: 2px solid #eee;
            transition: .3s border-color;
            display: none;
            height: 45px;
        }

        .type-2:hover {
        border: 2px solid #aaa;
        border-radius:10px;
        }
        .sub-judul{
        border-radius:10px;
        border: 2px solid #eee;
        height: 45px;
        }
        body { 
            font-family: 'Helvetica Neue', sans-serif; 
            /* font-size: 14px; 
            line-height: 24px;
             margin: 0 0 24px;  */
             text-align: justify; 
             text-justify: inter-word; 
        }
        .bootstrap-select .select-with-transition, .bootstrap-select .btn:active, .bootstrap-select .btn.active {
	        background-image: linear-gradient(to top, #1363B4 2px, rgba(156, 39, 176, 0) 2px), linear-gradient(to top, rgba(249, 249, 249, 0.26) 1px, transparent 1px);
        }

        .btn-pill-2 {
        border-radius: 50px;
        }

        .btn-info-2 {
        background-color: #1363B4;
        border-color: #1363B4;
        color: #fff;
        }

        .btn-info-2:hover {
        background-color: #0f4e99;
        border-color: #0f4e99;
        }
        .cart{
            display: none;
        }
        .rounded-btn{
            border-radius:10px;
            border: 1px solid #090170 !important;
        }
        .rounded-textarea{
            border-radius:10px;
            border: 2px solid #eee;
            transition: .3s border-color;
            height: 45px;
        }
        .btn-res {
            width: 100%;
            min-width: 130px;  
            max-width: 230PX;
        }

        .custom-select {
            position: relative;
            width: 100%;
            height: 45px;
            overflow: hidden;
            background-color: #FAFAFA;
            border: 2px solid #eee;
            transition: .3s border-color;
            border-radius:10px;
        }

            /* Gaya untuk select */
        .custom-select select {
            width: 100%;
            padding: 10px;
            border: none;
            outline: none;
            background: transparent;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

            /* Gaya untuk arrow icon */
        .custom-select::after {
            content: "\25BC";
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            font-size: 14px;
        }

            /* Gaya untuk option */
            .custom-select select option {
            background-color: #fff;
            position: relative;
            width: 100px;
            color: #818181;
        }

            /* Gaya saat select di-hover */
            .custom-select:hover {
            border-color: #aaa;
        }

            /* Gaya saat option di-hover */
            .custom-select select option:hover {
            background-color: #f0f0f0;
        }

            /* Gaya saat select di-focus */
        .custom-select select:focus {
            border-color: #007bff;
        }
        @media (max-width: 767px) {
            .custom-select {
                max-width: 100%; /* Mengisi lebar layar pada perangkat dengan lebar maksimal 767px */
            }
        }
    </style>

</head>

<body class="landing-page" style="background-color:#ffffff">
    <div class="page-header header-filter" data-parallax="true">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="title"></h1>
                </div>
            </div>
        </div>
    </div>
    <div class="main">
        <div class="container">
            <div class="section">
                <div class="row mb-3">
                    <div class="col-md-8 mr-auto">
                        <b class="text-left p-text text-custom-primary-color text-lg-left">PA Perjalanan</b>
                    </div>
                </div>
                <div class="features">
                    <div class="row mb-3">
                        <div class="col-md-8 mr-auto">
                            <b class="text-left p-text text-custom-primary-color text-lg-left">Periode Asuransi 14 Hari</b>
                        </div>
                    </div>
                    <form class="formAction">
                        <div class="mt-2">
                            <label for="startDate1" class="text-dark text-md-left">TANGGAL PERGI</label>
                            <div class="input-group has-primary">
                                <input type="text" class="form-control datepicker bg-section type-1 pl-4"
                                    id="startDate1" name="startDate1" value="" placeholder="TANGGAL PERGI "
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class=" mt-2">
                            <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="jenisKendaraan1"
                                title="JENIS KENDARAAN" id="jenisKendaraan1" onchange="displayBiaya()" required>
                                <option value="Asuransi Motor">Roda 2</option>
                                <option value="Asuransi Mobil">Roda 4</option>
                            </select>
                        </div>
                        <div class="">
                            <div class="form-group">
                                <div class="input-group">
                                    <b class="mt-3" id="motor" style="display: none">Rp 35.000</b>
                                    <b class="mt-3" id="mobil" style="display: none">Rp 75.000</b>
                                </div>
                            </div>
                        </div>
                        <div class="input-group pt-2">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4" name="asal1" id="asal1"
                                placeholder="ASAL" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4" name="tujuan1" id="tujuan1"
                                placeholder="TUJUAN" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4" name="nama1" id="nama1"
                                placeholder="NAMA LENGKAP" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4" name="passport1" id="passport1"
                                placeholder="KTP/PASSPORT" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="email" class="form-control bg-section text-dark type-1 pl-4 email" name="email1" id="email1"
                                placeholder="EMAIL" />
                        </div>
                        
                        <div class="input-group has-primary pt-3">
                            <input type="text" class="form-control datepicker bg-section  datepicker tanggalLahir type-1 pl-4" name="tanggalLahir1" id="tanggalLahir1"
                                placeholder="TANGGAL LAHIR" />
                        </div>
                        <div id="textareaTags">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group label-floating">
                                        <textarea class="form-control rounded-textarea bg-section pl-4" name="alamat1" rows="3" id="alamat1" placeholder="ALAMAT"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label for="" class="text-dark text-md-left mt-3">PASANGAN</label>

                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="namaPasangan1" id="namaPasangan1"
                                placeholder="NAMA PASANGAN" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="" class="form-control bg-section text-dark type-1 pl-4 " name="passportPasangan1" id="passportPasangan1"
                                placeholder="KTP/PASSPORT Pasangan" />
                        </div>
                        <div class="input-group has-primary pt-3">
                            <input type="text" class="form-control datepicker bg-section  datepicker tanggalLahirPasangan1 type-1 pl-4" name="tanggalLahirPasangan1" id="tanggalLahirPasangan1"
                                placeholder="TANGGAL LAHIR Pasangan" />
                        </div>

                        <label for="" class="text-dark text-md-left mt-3">ANAK 1</label>

                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="namaAnak11" id="namaAnak11"
                                placeholder="NAMA ANAK 1" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="passportAnak11" id="passportAnak11"
                                placeholder="KTP/PASSPORT Anak 1" />
                        </div>
                        <div class="input-group has-primary pt-3">
                            <input type="text" class="form-control datepicker bg-section  datepicker tanggalLahirAnak11 type-1 pl-4" name="tanggalLahirAnak11" id="tanggalLahirAnak11"
                                placeholder="TANGGAL LAHIR Anak 1" />
                        </div>

                        <label for="" class="text-dark text-md-left mt-3">ANAK 2</label>

                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="namaAnak21" id="namaAnak21"
                                placeholder="NAMA ANAK 1" />
                        </div>
                        <div class="input-group pt-3">
                            <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="passportAnak21" id="passportAnak21"
                                placeholder="KTP/PASSPORT Anak 1" />
                        </div>
                        <div class="input-group has-primary pt-3">
                            <input type="text" class="form-control datepicker bg-section  datepicker tanggalLahirAnak21 type-1 pl-4" name="tanggalLahirAnak21" id="tanggalLahirAnak21"
                                placeholder="TANGGAL LAHIR Anak 1" />
                        </div>
                        <div class="policyHolder">
                            <div class="section">
                                <div class="features ">
                                        <img src="{{ asset('assets/img/loading-gif.gif') }}" width="100" class="mt-4 loading-bar" style="display:none;" alt="loadingbar">
                                        <button type="button" class="btn btn-round mt-2 btn-sm-block bg-primary-2 w-100 submit-button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" onclick="myModal()"><b>KIRIM</b></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <br>
            <center><img src="{{ asset('assets/Keranjang.png')}}" alt="keranjang" width="80px" height="80px"></center>
            <center><h5><b>Pilih Keranjang</b></h5></center>
            <form class="formAction" onsubmit="event.preventDefault(); submitForm(this)">
                <div class="modal-body">
                    <input type="hidden" value="{{ $product_id }}" name="salvus_product_id">
                    <input type="hidden" class="form-control " name="startDate" id="startDate"/>
                    <input type="hidden" class="form-control " name="jenisKendaraan" id="jenisKendaraan"/>
                    <input type="hidden" class="form-control " name="asal" id="asal"/>
                    <input type="hidden" class="form-control " name="tujuan" id="tujuan"/>
                    <input type="hidden" class="form-control " name="nama" id="nama"/>
                    <input type="hidden" class="form-control " name="passport" id="passport"/>
                    <input type="hidden" class="form-control " name="email" id="email"/>
                    <input type="hidden" class="form-control " name="tanggalLahir" id="tanggalLahir"/>
                    <input type="hidden" class="form-control " name="namaPasangan" id="namaPasangan"/>
                    <input type="hidden" class="form-control " name="passportPasangan" id="passportPasangan"/>
                    <input type="hidden" class="form-control " name="tanggalLahirPasangan" id="tanggalLahirPasangan"/>
                    <input type="hidden" class="form-control " name="namaAnak1" id="namaAnak1"/>
                    <input type="hidden" class="form-control " name="passportAnak1" id="passportAnak1"/>
                    <input type="hidden" class="form-control " name="tanggalLahirAnak1" id="tanggalLahirAnak1"/>
                    <input type="hidden" class="form-control " name="namaAnak2" id="namaAnak2"/>
                    <input type="hidden" class="form-control " name="passportAnak2" id="passportAnak2"/>
                    <input type="hidden" class="form-control " name="tanggalLahirAnak2" id="tanggalLahirAnak2"/>
                    <textarea class="form-control " name="alamat" id="alamat" rows="3" hidden></textarea>
                    @if(!empty($list))
                        <input type="hidden" class="form-control bg-section text-dark type-1 pl-4 " name="listCart" id="listCart" value="{{ $list->id }}" readonly/>
                        <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="nameCart" id="nameCart" value="{{ $list->name }}" readonly/>
                    @else
                    <div class="row text-center">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="btn-group">
                                    <button type="button" style="width: 130px" class="btn btn-round btn-info btn-sm w-100 rounded-btn btn-res" id="keranjangBaru"
                                        value="keranjangBaru"> KERANGJANG BARU</button>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control bg-section text-dark type-1 pl-4 " name="newCart" id="newCart"
                            placeholder="NAMA KERANJANG BARU"/>
                    </div>
                    @endif
                <br>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-btn btn-res" data-bs-dismiss="modal">TUTUP</button> &nbsp;
                    <button type="submit" class="btn btn-primary submit-button rounded-btn btn-res" id="submitMudik"><b>KIRIM</b></button>
                </div>
            </form>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="{{ asset('assets/travel/js/material-kit.js?v=2.2.0 ')}}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('#startDate1').datetimepicker({
            format: 'DD MMM YYYY',
            minDate: new Date().setDate(new Date().getDate()),
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
        $('#tanggalLahir1').datetimepicker({
        format: 'DD MMM YYYY',
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

        $('#tanggalLahirPasangan1').datetimepicker({
        format: 'DD MMM YYYY',
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
        $('#tanggalLahirAnak11').datetimepicker({
        format: 'DD MMM YYYY',
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
        $('#tanggalLahirAnak21').datetimepicker({
        format: 'DD MMM YYYY',
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
    </script>
    <script>
        function displayBiaya() {
            const jenis = document.getElementById("jenisKendaraan1").value;
            console.log(jenis);
            if (jenis == 'Asuransi Motor') {
                document.querySelector('#motor').style.display = 'inline-block';
                document.querySelector('#mobil').style.display = 'none';
            }else{
                document.querySelector('#mobil').style.display = 'inline-block';
                document.querySelector('#motor').style.display = 'none';
            }
        }
    </script>
    <script>
        function myModal() {
            const startDate2 = document.getElementById("startDate1").value;
            const jenisKendaraan2 = document.getElementById("jenisKendaraan1").value;
            const asal2 = document.getElementById("asal1").value;
            const tujuan2 = document.getElementById("tujuan1").value;
            const nama2 = document.getElementById("nama1").value;
            const passport2 = document.getElementById("passport1").value;
            const email2 = document.getElementById("email1").value;
            const tanggalLahir2 = document.getElementById("tanggalLahir1").value;
            const namaPasangan2 = document.getElementById("namaPasangan1").value;
            const passportPasangan2 = document.getElementById("passportPasangan1").value;
            const tanggalLahirPasangan2 = document.getElementById("tanggalLahirPasangan1").value;
            const namaAnak12 = document.getElementById("namaAnak11").value;
            const passportAnak12 = document.getElementById("passportAnak11").value;
            const tanggalLahirAnak12 = document.getElementById("tanggalLahirAnak11").value;
            const namaAnak22 = document.getElementById("namaAnak21").value;
            const passportAnak22 = document.getElementById("passportAnak21").value;
            const tanggalLahirAnak22 = document.getElementById("tanggalLahirAnak21").value;
            const alamat2 = document.getElementById("alamat1").value;
            console.log(jenisKendaraan2);
            document.getElementById("startDate").value = startDate2;
            document.getElementById("jenisKendaraan").value = jenisKendaraan2;
            document.getElementById("asal").value = asal2;
            document.getElementById("tujuan").value = tujuan2;
            document.getElementById("nama").value = nama2;
            document.getElementById("passport").value = passport2;
            document.getElementById("email").value = email2;
            document.getElementById("tanggalLahir").value = tanggalLahir2;
            document.getElementById("namaPasangan").value = namaPasangan2;
            document.getElementById("passportPasangan").value = passportPasangan2;
            document.getElementById("tanggalLahirPasangan").value = tanggalLahirPasangan2;
            document.getElementById("namaAnak1").value = namaAnak12;
            document.getElementById("passportAnak1").value = passportAnak12;
            document.getElementById("tanggalLahirAnak1").value = tanggalLahirAnak12;
            document.getElementById("namaAnak2").value = namaAnak22;
            document.getElementById("passportAnak2").value = passportAnak22;
            document.getElementById("tanggalLahirAnak2").value = tanggalLahirAnak22;
            document.getElementById("alamat").value = alamat2;
        }
    </script>
    <script>
        $("#keranjangBaru").click(function () {
            var keranjangBaru = $(this).val();
            document.getElementById("keranjangBaru").className = 'btn btn-round btn-info-2 btn-sm w-100 rounded-btn btn-res';
            document.getElementById("listKeranjang").className = 'btn btn-round btn-info btn-sm w-100 rounded-btn text-left btn-res';
            document.querySelector('#newCart').style.display = 'inline-block';
            document.querySelector('.cartList').style.display = "none";
        });
        $("#listKeranjang").click(function () {
            var listKeranjang = $(this).val();
            document.getElementById("keranjangBaru").className = 'btn btn-round btn-info btn-sm w-100 rounded-btn btn-res';
            document.getElementById("listKeranjang").className = 'btn btn-round btn-info-2 btn-sm w-100 rounded-btn text-left btn-res';
            document.querySelector('#newCart').style.display = 'none';
            document.querySelector('.cartList').style.display = 'inline-block';
        });

    </script>
    @if(session()->has('success'))
        <script>
            callback.postMessage('/');
        </script>
    @endif

    <script>
        function submitForm(form) {
            let data = $(form).serializeArray();
            console.log(data);

            let obj = {};
            for (index in data) {
                obj[data[index].name] = data[index].value;
            };
            $(".loading-bar").show();
            $(".submit-button").hide();
            $.ajax({
                type : "POST",
                data: obj,
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('perjalanan.submit') }}",
                success: function(res) {
                    console.log(res);
                    Swal.fire({
                        title: 'Berhasil',
                        text: "Pesanan berhasil dibuat",
                        icon: 'success',
                        showCancelButton: false,
                        customClass: {
                            confirmButton: 'btn btn-pill-2 btn-block w-100 rounded-pill btn-info-2'
                        },
                        confirmButtonText: 'Tutup'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            callback.postMessage('/');
                        }
                    })
                },
                error: function(err, textStatus, errorThrown) {
                    let res = JSON.parse(err.responseText);
                    console.log(res);
                    $(".loading-bar").hide();
                    $(".submit-button").show();
                    if(err.status == 422) {
                        Swal.fire({
                            icon: 'error',
                            title: '<strong>Validation Errors</strong>',
                            html:
                                '<div>' +
                                ' <ul class="validation_errors"> ' +
                                '</ul>' + 
                                '</div>',
                            showCloseButton: true,
                            focusConfirm: false,
                        })
                        
                        for(key in res.data) {
                            $('.validation_errors').append(`<li>`+res.data[key][0]+`</li>`);
                        }
                    }else {
                        Swal.fire({
                                icon: 'error',
                                title: 'Oops!..',
                                text: res.message,
                            customClass: {
                                confirmButton: 'btn btn-pill-2 btn-block w-100 rounded-pill btn-info-2'
                            },
                            buttonsStyling: false,
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
                            hideClass: {
                                popup: '',
                                backdrop: ''
                            }
                        })
                    }
                }
            })
        }
    </script>
</body>

</html>