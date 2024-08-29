<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Travellin - ZIPro</title>
    <link href="{{ asset('assets/travel/css/material-kit.css?v=2.2.0') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/demo.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/travel/demo/vertical-nav.css') }}" rel="stylesheet" />
    @include('travel.styles')
</head>
<body>
    <div class="container" style="min-height: 100vh">
        <div class="row container-loader my-auto">
            <div class="col-md-12 my-auto text-center">
                <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalError" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalPenawaranLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="FormPenawaran">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container text-center">
                            <h3 class="title" style="font-size: 14px;"> Terjadi Kesalahan </h3>
                            <p>{{ $message }}</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between mt-2">
                        <button type="button" class="btn btn-info-2 bg-primary-2 btn-close-error" data-dismiss="modal">TUTUP</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/travel/js/core/jquery.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/core/popper.min.js ')}}" type="text/javascript"></script>
    <script src="{{ asset('assets/travel/js/core/bootstrap-material-design.min.js ')}}" type="text/javascript"></script>

    <script>
        $(function() {
            setTimeout(() => {
                $("#modalError").modal('show');
            }, 2000);
        });
    </script>
    <script>
        $(".btn-close-error").click(function() {
            callback.postMessage('/');
        })
    </script>
</body>
</html>