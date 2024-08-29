<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Referral</title>
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
                        {{-- <h4 class="text-primary"><b>DAFTAR</b></h4> --}}
                        <br>
                        <p class="">Terima Kasih sudah mendaftar ke platform kami, untuk mendapatkan benefit lebih
                            silahkan lengkapi data diri pada menu profile di aplikasi ZIPro</p>
                        <br>
                        <p><b>DOWNLOAD DI SINI</b></p>
                        <a href="https://play.google.com/store/apps/details?id=com.salvus.zap"><img
                                src="{{ asset('assets/img/play-store.png') }}" alt="Google Play Store"
                                style="width: 40%"></a>
                        <a href="https://apps.apple.com/id/app/zipro/id1660517994?l=id"><img
                                src="{{ asset('assets/img/app_store.png') }}" alt="App Store" style="width: 55%"></a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>

</html>
