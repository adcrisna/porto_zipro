<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <title>Travel Insurance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css"
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css"> --}}
    <style>
        :root {
            --custom-primary-color: #1363B4;
        }

        .btn-primary,
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

        .bg-primary {
            background-color: var(--custom-primary-color);
        }
        .text-custom-primary-color {
            color: var(--custom-primary-color);
        }

        :root {
        --blue-100: hsl(204, 86%, 90%);
        --blue-200: hsl(204, 80%, 80%);
        --blue-300: hsl(204, 72%, 70%);
        --blue-400: hsl(204, 64%, 60%);
        --blue-500: hsl(204, 86%, 50%);
        --blue-600: hsl(204, 100%, 40%);
        --blue-700: hsl(204, 100%, 35%);
        --blue-800: hsl(204, 100%, 30%);
        --blue-900: hsl(204, 100%, 25%);
        }
    </style>
  </head>
  <body>
    <div class="container-sm">
        <div class="row g-3">
            <form action="">
                <div class="col-md-12 col-lg-12 col-xl-12">
                    <div class="pt-3 pb-2">
                        <p class="text-custom-primary-color fs-5 fw-bolder" style="text-color:#1363B4">Asuransi Travel</p>
                    </div>
                    <div class="border p-2 mb-2 border-opacity-50 rounded">USIA DI BAWAH ATAU DI ATAS 70 TAHUN?</div>
                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="age" id="ageYes" value="yes">
                            Ya
                            <span class="circle">
                                <span class="check"></span>
                            </span>
                        </label>
                    </div>
                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="age" id="ageNo" value="no">
                            Tidak
                            <span class="circle">
                                <span class="check"></span>
                            </span>
                        </label>
                    </div>
                    <div id="formAlreadyTrav" class="mt-2" style="display: none;">
                        <div class="border p-2 mb-2 border-opacity-50 rounded">SUDAH TRAVELLING ATAU BELUM?</div>
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravYes"
                                    value="yes">
                                Ya
                                <span class="circle">
                                    <span class="check"></span>
                                </span>
                            </label>
                        </div>
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravNo"
                                    value="no">
                                Tidak
                                <span class="circle">
                                    <span class="check"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="alreadytravel-date mt-2" style="display: none;">
                        <div class="input-group">
                            <input type="text" class="form-control mb-2 " placeholder="TANGGAL KEBERANGKATAN" aria-label="TANGGAL PERGI" aria-describedby="basic-addon2" id="inputdepartureDate">
                        </div>
                        <div class="card card-disclaim mb-2" id="card-disclaim" style="display: none;">
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

                                <div class="form-check">
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
                    <div class="formDestination mt-2" style="display:none;">
                        <div class="input-group">
                            <label for="startDate" class="form-label">TANGGAL PERGI</label>
                            <div class="input-group" >
                                <input type="text" required class="form-control mb-2 " value="{{ date('d-m-Y') }}" placeholder="TANGGAL PERGI" aria-label="TANGGAL PERGI" aria-describedby="basic-addon2" id="startDate">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="endDate" class="form-label">TANGGAL PULANG</label>
                            <div class="input-group">
                                <input type="text " required class="form-control  mb-2" value="{{ date('d-m-Y') }}" placeholder="TANGGAL PULANG" aria-label="TANGGAL PERGI" aria-describedby="basic-addon2" id="endDate">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="days" class="form-label">HARI</label>
                            <div class="input-group">
                                <input type="text" class="form-control  col-sm-3 days" name="days" id="days" value="1" required>
                            </div>
                        </div>

                        <select class="form-select mt-2 desArea" data-live-search="true" id="destinationArea" data-style="select-with-transition" name="destinationArea" title="Destination Area" required>
                            <option selected  disabled>AREA TUJUAN</option>
                            @foreach($regions['Regions' ] as $key => $value)
                            <option value="{{ $value['ID'] }}">{{ $value['Name'] }}</option>
                            @endforeach
                        </select>
                        <div class="input-group divcountry">
                            <select class="form-select desCountry mt-2" data-live-search="true"
                                            data-style="select-with-transition" name="destinationCountry"
                                            title="Destination Country" data-size="7">
                                <option  selected>NEGARA TUJUAN</option>
                                <option>Please select Destination Area first</option>
                            </select>
                        </div>
                        
                        <select class="form-select mt-2 typePlan" data-live-search="true" data-style="select-with-transition" name="package_type" title="Package Types" data-size="7" required>
                            <option selected disabled>TIPE PAKET</option>
                            @foreach ($types as $type)
                                <option value="{{ $type['ID'] }}">{{ $type['Name'] }}</option>
                            @endforeach
                        </select>
                        <select class="form-select mt-2"  data-live-search="true" data-style="select-with-transition" name="travel_need" title="Travel Needs" data-size="7" required>
                            <option selected disabled>KEPERLUAN PERJALANAN</option>
                            @foreach ($travelneeds as $travelneed)
                                <option value="{{ $travelneed['ID'] }}">{{ $travelneed['Name'] }}</option>
                            @endforeach
                        </select> 
                        <select class="form-select mt-2"  data-live-search="true" data-style="select-with-transition" name="origins" title="Origins" data-size="7" required>
                            <option selected disabled>KOTA ASAL</option>
                            @foreach ($origins as $origin)
                                <option value="{{ $origin['ID'] }}">{{ $origin['Name'] }}</option>
                            @endforeach
                        </select>
                        {{-- <div class="row mt-5">
                            <div class="col-sm-12 text-center">
                                <button class="btn btn-info btn-submit-section" type="button">
                                    submit
                                </button>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="{{ asset('assets/travel/js/plugins/bootstrap-selectpicker.js ')}}" type="text/javascript"></script>

    <script>
        // $('.selectpicker').selectpicker();

        $("input[name=age]").on('change', function () {
            $('#formAlreadyTrav').show();
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
    </script>
    <script>
        var today = new Date(); 
        $('#inputdepartureDate').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: '-3d', 
            endDate: today
        });

        $('#startDate').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: today
        });

        $('#endDate').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            startDate: today
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

        $('#inputdepartureDate').change(function() {
                $('#card-disclaim').show();
        });

        
        
        $('#startDate').on('change', function (e) {
            var tglPergi = $("#startDate").datepicker("getDate");
            var tglPulang = $("#endDate").datepicker("getDate");
            if (tglPulang < tglPergi) {
                $("#endDate").datepicker("setDate", tglPergi);
                tglPulang = tglPergi;
            }
            updateJumlahHari(tglPergi,tglPulang);
        });

        $('#endDate').on('change', function (e) {u
            var tglPergi = $("#startDate").datepicker("getDate");
            var tglPulang = $("#endDate").datepicker("getDate");
            updateJumlahHari(tglPergi,tglPulang);
        });

        $('#days').on('input', function (e) {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
            var tglPergi = $("#startDate").datepicker("getDate");
            var jmlHari = parseInt($("#days").val());
            var tglPulang = new Date(tglPergi.getTime() + (jmlHari - 1) * 24 * 60 * 60 * 1000);
            $("#endDate").datepicker("setDate", tglPulang);
        });

        function updateJumlahHari(startDate, endDate) {
            var tglPergi = startDate;
            var tglPulang = endDate;
            
            if (tglPergi != null && tglPulang != null) {
                var diffTime = Math.abs(tglPulang.getTime() - tglPergi.getTime());
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                $("#days").val(diffDays);
            }
        }


    </script>
    <script>
        $('.desArea').on('change', function (e) {
            $('.desCountry').remove();
            let val = $(this).find(":selected").val()
            $('.divcountry').append('<i class="fa fa-spinner fa-4x fa-spin  text-info"></i>')
            $('.divcountry').append(
                `<select class="form-select selectpicker desCountry mt-2" data-live-search="true" data-style="select-with-transition" name="destinationCountry" title="Destination Country" data-size="7"></select>`
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
                    console.log(res);
                    res['country'].forEach((v, k) => {
                        $('.divcountry').find('i').remove();
                        $('select.desCountry').append(`<option value="` + v.ID + `">` + v.Name + `</option>`)
                        $('select.desCountry');
                    });
                }
            });
        });
    </script>
    </body>
</html>
