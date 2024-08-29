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
    <style>
        .form-check .form-check-input[disabled] + .circle .check {
            background-color: #0d59fc;
        }
        .form-check .form-check-input[disabled] ~ .check, .form-check .form-check-input[disabled] ~ .circle {
            opacity: 1;
        }
    </style>

</head>

<body class="landing-page" style="background-color:#ffffff">
    <div class="main">
        <div class="container ">
            <div class="section">
                <div class="row mb-3 header-product" style="">
                    <div class="d-flex">
                        <div class="col-md-3 mr-auto text-center">
                            <img src="{{ asset('uploads/product/'.$product->logo) }}" alt="logo" class="img-fluid">
                            <b class="text-center p-text text-custom-primary-color">{{ $product->display_name }}</b>
                        </div>
                        <div class="col-md-9 my-auto">
                            <p class="p-text text-custom-primary-color" style="font-size:20px"> {{ $product->description }} </p>
                        </div>
                    </div>
                </div>
                <div class="form__input" style="">
                    <form class="formAction" onsubmit="event.preventDefault(); initSubmit(this)" novalidate>
                        <input type="hidden" value="{{ $product_id }}" name="salvus_product_id">
                    
                        <div class="mt-2">
                            <div class="input-group has-primary">
                                <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="birthDate"
                                    value="" placeholder="TANGGAL LAHIR PESERTA" name="birth" autocomplete="off">
                            </div>
                            <p class="text-danger mt-2">
                                Family: Usia tertanggung (dewasa) maks. 70 tahun & (anak) maks. 18 tahun <br>
                                Duo Plus: Usia tertanggung maks. 70 tahun</p>
                        </div>
                        <div class="mt-3">
                            <select class="selectpicker w-100 pb-5 text-dark origins bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="origins" title="KOTA ASAL"
                                data-size="7" required>
                                @foreach ($origins as $origin)
                                    <option value="{{ $origin['ID'] }}">{{ $origin['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=" mt-3">
                            <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="destinationArea"
                                title="AREA TUJUAN" required>
                                @foreach($regions['Regions' ] as $key => $value)
                                    <option value="{{ $value['ID'] }}">{{ $value['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=" mt-3 divcountry text-center">
                            <select class="selectpicker w-100 pb-5 text-dark desCountry bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="destinationCountry"
                                title="Negara / Kota Tujuan" data-size="7">
                                <option disabled>Pilih area tujuan untuk melanjutkan..</option>
                            </select>

                        </div>
                        <div id="formAlreadyTrav" class="mt-3">
                            <div class="sub-judul mb-1 bg-section shadow-none">
                                <div class="font-weight-normal pt-2 pl-2 text-left px-3">
                                    <p class="p-text">
                                        SUDAH TRAVELLING ATAU BELUM?
                                    </p>
                                </div>
                            </div>
                            <div class="form-check form-check-radio mt-3">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravYes"
                                        value="yes" disabled>
                                    Ya
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravNo"
                                        value="no" disabled>
                                    Tidak
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="alreadytravel-date mt-4" style="display: none;">
                            <div class="form-group pt-0">
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control datepicker bg-section  type-1 pl-4" name="depature_date" id="inputdepartureDate"
                                        placeholder="TANGGAL BERANGKAT" autocomplete="off" value=""/>
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

                                    <div class="form-check mt-3">
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
                        <div class="formDestination mt-3" style="display:none;">
                            <div class="mt-2 divTypePlan">
                                <label class="text-dark text-md-left">TIPE PAKET</label>
                                <select class="selectpicker w-100 pb-5 text-dark typePlan bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="package_type" title="TIPE PAKET"
                                    data-size="7" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type['ID'] }}">{{ $type['Name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-2">
                                <label for="startDate" class="text-dark text-md-left">TANGGAL PERGI</label>
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="startDate"
                                        value="" placeholder="TANGGAL PERGI " autocomplete="off" disabled>
                                </div>
                            </div>
                            <div class="mt-2" >
                                <label for="endDate" class="text-dark text-md-left">TANGGAL PULANG</label>
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control type-1 datepicker pl-4 endDate enddatete bg-section" id="endDate"
                                        value="" placeholder="TANGGAL PULANG" autocomplete="off" disabled/>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label for="days" class="text-dark text-md-left">HARI</label>
                                <input type="number" class="form-control type-1 pl-4 days text-dark bg-section" name="days" min="1" id="days"
                                    value="" placeholder="Hari" readonly>
                            </div>

                            <label for="type_product" class="text-dark text-md-left mt-3 ">TIPE PRODUK</label>
                            <div class="divTypeProduct">
                                <select id="type_product" class="selectpicker w-100 pb-5 text-dark typeProduct bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="product_type" title="TIPE PRODUK"
                                    data-size="7" required>
                                        <option value="Individual">INDIVIDU</option>
                                        <option value="Family">KELUARGA</option>
                                        <option value="Duo Plus">DUO PLUS</option>
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="travelneeds" class="text-dark text-md-left">KEPERLUAN PERJALANAN</label>
                                <select id="travelneeds" class="selectpicker w-100 pb-5 text-dark travelneeds bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="travel_need" title="KEPERLUAN PERJALANAN"
                                    data-size="7" required>
                                    @foreach ($travelneeds as $travelneed)
                                        <option value="{{ $travelneed['ID'] }}">{{ $travelneed['Name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-5">
                                <div class="col-sm-12 text-center">
                                <button class="btn btn-info-2 btn-round btn-sm-block bg-primary-2 btn-submit-section">Lihat produk</button>
                                </div>
                            </div>
                        </div>
                        <div class="productResult mt-5 pt-4"></div>
                        <div class="additional_coverage mt-3"></div>
                    </form>
                    <div class="mt-5">
                        <div class="col-sm-12 text-center">
                            <button class="btn btn-info-2 btn-round bg-primary-2 btn-continue btn-block" style="display: none;">Lanjut</button>
                        </div>
                    </div>
                </div>
                <div class="form__summary" style="display: none">
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-md-12 ml-auto mr-auto m-2">
                                <div class="card">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-md-12 mt-2">
                                                <span id="sum-product-cat" class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                                                    
                                                </span>
                                                <span id="sum-product-plan" style="color:#263570; font-weight: bold; font-size: 16px">
                                                    
                                                </span>
                                                <table class="table table-borderless mt-2">
                                                    <thead>
                                                        <tr style="">
                                                            <td scope="col" style="color:#263570; width: 250px;">
                                                                <u style="font-size: 15px;font-weight: bold;">Kelompok Umur</u>
                                                                <br>
                                                                <span id="sum-product-umur" style="font-size: 13px;"> +- 70 Tahun </span>
                                                            </td>
                                                            <td scope="col" style="color:#263570; font-weight: bold; font-size: 15px; width: 250px;">
                                                                <u>Jumlah Peserta</u>
                                                                <br>
                                                                <span style="font-size: 13px;"> 1 </span>
                                                            </td>
                                                            <td scope="col" style="color:#0CBC8B; font-weight: bold; font-size: 15px;">
                                                                <u>Premi</u>
                                                                <br>
                                                                <span id="sum-product-pax" style="font-size: 13px;">  </span>
                                                            </td>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                            <div class="table_sum">

                                            </div>

                                            <div class="col-md-12 mt-4">
                                                 <div class="sub-judul bg-section mb-1 shadow-none">
                                                    <div class="font-weight-normal pt-2 pl-3 pr-3 text-left">
                                                        <div class="d-flex justify-content-between">
                                                            <p class="p-text" style="color:#0CBC8B; font-weight: bold;">
                                                                Total Biaya Perlindungan
                                                            </p>
                                                            <p class="p-text" id="total-price" style="color:#0CBC8B; font-weight: bold;">
                                                                
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="row">
                            <div class="col-6 text-center">
                                <button class="btn btn-secondary-2 btn-round bg-primary-2 btn-penawaran" data-toggle="modal" data-target="#modalPenawaran" style="width: 170px;">Penawaran</button>
                            </div>
                            <div class="col-6 text-center">
                                <button class="btn btn-info-2 btn-round bg-primary-2 btn-penutupan" style="width: 170px;">Lanjut</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form__penutupan" style="display: none">
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
                                        <option value="Child">Anak</option>
                                        <option value="Family">Keluarga</option>
                                        <option value="Friend">Teman</option>
                                        <option value="Other">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="container-form-penutupan"></div>
                            <div class="sub-judul bg-section mb-1 shadow-none mt-4 w-75 form-add-pasangan">
                               <div class="d-flex  font-weight-normal pl-2 text-left">
                                    <button class="btn btn-info-2 btn-sm btn-add-family" type="button">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <p class="mt-2 ml-4">TAMBAH DATA</p>
                               </div>
                            </div>
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

    {{-- <div class="modal fade" id="modalTnC" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalTnCLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <embed src="{{ asset('assets/travel/Riplay_Zurich_Travel_Insurance_-_Umum.pdf') }}" frameborder="0" width="100%" height="500px">
                </div>
                <div class="modal-footer justify-content-between mt-2">
                    <button type="button" class="btn btn-info-2 bg-primary-2" data-dismiss="modal">TUTUP</button>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="modalPenawaran" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalPenawaranLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="FormPenawaran">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container text-center">
                            <h3 class="title" style="font-size: 14px;"> Silahkan isi data penerima penawaran </h3>
                            <div class="mt-2">
                                <div class="form-group">
                                    @if (!empty($carts))
                                        <input type="hidden" class="form-control type-1 pl-4 text-dark bg-section" name="cart" value="{{ $carts->id }}" readonly>
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" value="{{ $carts->name }}" readonly>
                                    @else
                                        <select class="selectpicker w-100 text-dark pb-3 bg-section type-1 selectedCartPenawaran" data-live-search="true"
                                            data-style="select-with-transition" name="cart"
                                            title="PILIHAN KERANJANG" required>
                                            <optgroup label="New Cart">
                                                <option value="new_cart">Buat keranjang baru</option>
                                            </optgroup>
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <input type="text" class="form-control type-1 pl-4 text-dark bg-section cartNamePenawaran" name="cartUser" id="cartUser"
                                    value="" placeholder="Nama Keranjang" style="display: none">
                            </div>
                            <div class="mt-2">
                                <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="offeringName" id="offeringName"
                                    value="" placeholder="Nama">
                            </div>
                            <div class="mt-2">
                                <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="offeringPhone" id="offeringPhone"
                                    value="" placeholder="No. Telepon">
                            </div>
                            <div class="mt-2">
                                <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="offeringMail" id="offeringMail"
                                    value="" placeholder="Email">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between mt-2">
                        <button type="button" class="btn btn-info-2 bg-primary-2" data-dismiss="modal">TUTUP</button>
                        <button type="button" class="btn btn-info-2 bg-primary-2 btn-submit-penawaran">Kirim</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="ModalListCart" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="ModalListCartLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="formListCart">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container text-center">
                            <h3 class="title" style="font-size: 14px;"> Silahkan pilih Keranjang Anda </h3>
                            <div class="mt-2">
                                <div class="form-group">
                                    @if (!empty($carts))
                                        <input type="hidden" class="form-control type-1 pl-4 text-dark bg-section" name="cart" value="{{ $carts->id }}" readonly>
                                        <input type="text" class="form-control type-1 pl-4 text-dark bg-section" value="{{ $carts->name }}" readonly>
                                    @else
                                        <select class="selectpicker w-100 text-dark pb-3 bg-section type-1 selectedCart" data-live-search="true"
                                            data-style="select-with-transition" name="cart"
                                            title="PILIHAN KERANJANG" required>
                                            <optgroup label="New Cart">
                                                <option value="new_cart">Buat keranjang baru</option>
                                            </optgroup>
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <input type="text" class="form-control type-1 pl-4 text-dark bg-section cartName" name="cartUser" id="cartUser"
                                    value="" placeholder="Nama Keranjang" style="display: none">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between mt-2">
                        <button type="button" class="btn btn-info-2 bg-primary-2" data-dismiss="modal">TUTUP</button>
                        <button type="button" class="btn btn-info-2 bg-primary-2 btnSubmitCart">Kirim</button>
                    </div>
                </div>
            </form>
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
    
    @include('travel.script')

</body>

</html>