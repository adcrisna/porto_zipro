<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Zipro Friend</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #F8F8FF;
        }

        .form-control {
            border-radius: 15px;
        }

        .text-primary {
            color: #000080 !important;
        }

        .border-primary {
            color: #000080 !important;
        }

        .btn-primary {
            background-color: #000080 !important;
        }

        .btn {
            border-radius: 15px;
        }

        .custom-file-label {
            overflow: hidden;
            white-space: nowrap;
            border-radius: 15px;
        }

        .file-name {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div id="intro">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center">
                        <h4 class="text-primary"><b>DAFTAR</b></h4>
                        <br>
                        <p class="">Apakah kamu sedang mencari PRODUK ASURANSI atau Ingin mereferensikan
                            PRODUK ASURANSI?</p>
                    </div>
                    <div class="text-center">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="radioCheck" value="enableRef"
                                id="radioCheck">
                            <label class="form-check-label">
                               Ingin Mereferensikan Produk Asuransi <br>
                            </label>
                            <span id="toggleButton1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                    <path
                                        d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
                                </svg>
                            </span>
                            <p id="textIcon1" style="font-size: 11px; display: none" class="text-info">Kamu hanya bisa
                                mereferensikan produk
                                asuransi dan <br>
                                tidak bisa berjualan.
                            </p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="radioCheck" value="enableSeller"
                                id="radioCheck">
                            <label class="form-check-label">
                                Mencari Produk Asuransi <br>
                            </label>
                            <span id="toggleButton2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                    <path
                                        d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
                                </svg>
                            </span>
                            <p id="textIcon2" style="font-size: 11px; display: none" class="text-info">Kamu bisa
                                berjualan produk
                                asuransi dan <br>
                                berkesempatan mengikuti
                                program
                                menarik lainnya.
                            </p>
                        </div>
                    </div>
                    <br>
                    <div class="text-center">
                        <button type="button" id="btnContinue" class="btn btn-primary" style="width: 150px"
                            id="daftar" disabled>Berikutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="registerRef" hidden="true">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center">
                        <img src="{{ asset('assets/img/zipro.png') }}" alt="Logo" class="mb-4"
                            style="max-width: 150px;">
                        {{-- <p>{{ bcrypt('password') }}</p> --}}
                    </div>
                    <form id="formRegister" method="POST" enctype="multipart/form-data" action="javascript:void(0)">
                        <div class="form-group">
                            <label class="text-primary">Nama Lengkap:</label>
                            <input type="text" class="form-control border-primary" id="name" name="name"
                                required>
                        </div>    
                        <div class="form-group">
                            <label class="text-primary">Email:</label>
                            <input type="email" class="form-control border-primary" id="email"
                                name="email"required>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Password:</label>
                            <input type="password" class="form-control border-primary" id="password" name="password"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Konfirmasi Password:</label>
                            <input type="password" class="form-control border-primary" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Alamat:</label>
                            <textarea name="alamat" id="alamat" cols="3" rows="2" class="form-control border-primary" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Kota:</label>
                            <input type="text" class="form-control border-primary" id="city" name="city"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Nomor Ponsel:</label>
                            <input type="number" class="form-control border-primary" id="phone" name="phone"
                                required>
                        </div>
                        <div class="form-group" style="display:none;">
                            <label class="text-primary">Referral Email:</label>
                            <input type="email" class="form-control border-primary" id="referrer_email"
                                name="referrer_email" value="{{ $ref_email }}">
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Bank:</label>
                            <select class="form-control border-primary" id="bank" name="bank">
                                <option value="">Pilih</option>
                                @foreach ($bank as $key => $value)
                                    <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="text-primary">Lokasi Cabang Bank:</label>
                            <input type="text" class="form-control border-primary" id="branch_location"
                                name="branch_location">
                        </div>
                        <div class="form-group">
                            <label class="text-primary">No Rekening:</label>
                            <input type="number" class="form-control border-primary" id="bank_acc_number"
                                name="bank_acc_number">
                        </div>
                         <div class="form-group">
                            <label class="text-primary">Nomor KTP:</label>
                            <input type="number" class="form-control border-primary" id="no_ktp"
                                name="no_ktp" required>
                        </div>
                        <input type="hidden" class="form-control border-primary" id="is_sf"
                            name="is_sf">
                        <input type="hidden" class="form-control border-primary" id="is_web"
                            name="is_web" value="1">
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" style="width: 150px"
                                id="daftar">Daftar</button>
                        </div>
                        <br>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        document.getElementById('id_card').addEventListener('change', function() {
            var fileName = this.files[0].name;
            document.getElementById('fileNameCard').innerText = 'File yang dipilih: ' + fileName;
        });
    </script>
    <script>
        document.getElementById('avatar').addEventListener('change', function() {
            var fileName = this.files[0].name;
            document.getElementById('fileNameAvatar').innerText = 'File yang dipilih: ' + fileName;
        });
    </script>
    <script>
        $(function() {
            $("#toggleButton1").on("click", function() {
                $("#textIcon1").toggle();
            });
        });
        $(function() {
            $("#toggleButton2").on("click", function() {
                $("#textIcon2").toggle();
            });
        });
    </script>
    <script>
        $(function() {
            $("input[name='radioCheck']").on("change", function() {
                var selectedValue = $("input[name='radioCheck']:checked").val();
                if (selectedValue === "enableRef") {
                    $('#is_sf').val(1);
                } else {
                    $('#is_sf').val(0);
                }
                console.log(selectedValue);
                if (selectedValue === "enableRef" || selectedValue === "enableSeller") {
                    $("#btnContinue").prop("disabled", false);
                } else {
                    $("#btnContinue").prop("disabled", true);
                }
            });
        });
    </script>
    <script>
        $(function() {
            $("#btnContinue").on("click", function() {
                $("#registerRef").prop("hidden", false);
                $("#intro").prop("hidden", true);
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function(e) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#formRegister').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                console.log(formData);
                $.ajax({
                    type: "POST",
                    data: formData,
                    url: "https://api-zap3.salvusnetwork.com/api/register",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        console.log('result'+res);
                        if (res.message == 'Validation errors') {
                            Swal.fire({
                                title: 'Gagal',
                                text: res.errors,
                                icon: 'error',
                                showCancelButton: false,
                                customClass: {
                                    confirmButton: 'btn btn-pill-2 btn-block w-100 rounded-pill btn-info-2'
                                },
                                confirmButtonText: 'Tutup'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    callback.postMessage('/');
                                }
                            });
                        } else if (res.message == 'Email Referensi belum terdaftar') {
                            Swal.fire({
                                title: 'Gagal',
                                text: res.errors,
                                icon: 'error',
                                showCancelButton: false,
                                customClass: {
                                    confirmButton: 'btn btn-pill-2 btn-block w-100 rounded-pill btn-info-2'
                                },
                                confirmButtonText: 'Tutup'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    callback.postMessage('/');
                                }
                            });
                        } else if (res.message == 'success') {
                            Swal.fire({
                                title: 'Berhasil',
                                text: "Registrasi Berhasil",
                                icon: 'success',
                                showCancelButton: false,
                                customClass: {
                                    confirmButton: 'btn btn-pill-2 btn-block w-100 rounded-pill btn-info-2'
                                },
                                confirmButtonText: 'Tutup'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location =
                                        "https://api-zap3.salvusnetwork.com/api/ref_done";
                                }
                            });
                        }
                    },
                    error: function(err, textStatus, errorThrown) {
                        let res = JSON.parse(err.responseText);
                        console.log(res.errors);
                        if (err.status == 422) {
                            Swal.fire({
                                icon: 'error',
                                title: '<strong>Validation Errors</strong>',
                                html: '<div>' +
                                    ' <ul class="validation_errors"> ' +

                                    '</ul>' +
                                    '</div>',
                                showCloseButton: true,
                                focusConfirm: false,
                            })

                            for (key in res.errors) {
                                $('.validation_errors').append(`<li>` + res.errors[key][0] +
                                    `</li>`);
                            }

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!..',
                                text: res.errors,
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
                });
            });
        });
    </script>
</body>

</html>
