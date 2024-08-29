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
                                    value="{{ $data['birth'] }}" placeholder="TANGGAL LAHIR PESERTA" name="birth">
                            </div>
                            <p class="text-danger mt-2">
                                Family: Usia tertanggung (dewasa) maks. 70 tahun & (anak) maks. 18 tahun <br>
                                Duo Plus: Usia tertanggung maks. 70 tahun</p>
                        </div>
                        <div class="mt-3 ">
                            <select class="selectpicker w-100 pb-5 text-dark origins bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="origins" title="KOTA ASAL"
                                data-size="7" required>
                                @foreach ($origins as $origin)
                                    <option value="{{ $origin['ID'] }}" {{ $data['origins'] == $origin['ID'] ? 'selected' : '' }}>{{ $origin['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="destinationArea"
                                title="AREA TUJUAN" required>
                                @foreach($regions['Regions' ] as $key => $value)
                                    <option value="{{ $value['ID'] }}" {{ $data['destinationArea'] == $value['ID'] ? 'selected' : '' }}>{{ $value['Name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=" mt-3 divcountry text-center">
                            <select class="selectpicker w-100 pb-5 text-dark desCountry bg-section type-1" data-live-search="true"
                                data-style="select-with-transition" name="destinationCountry"
                                title="NEGARA TUJUAN" data-size="7">
                                @foreach ($data['getCountry'] as $country)
                                    <option value="{{ $country['ID'] }}" {{ $data['destinationCountry'] == $country['ID'] ? 'selected' : '' }}>{{ $country['Name'] }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div id="formAlreadyTrav" class="mt-5">
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
                                        value="yes" {{ $data['destinationCountry'] == 1 ? "disabled" : "" }}>
                                    Ya
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="alreadyTrav" id="alreadyTravNo"
                                        value="no" {{ $data['destinationCountry'] == 1 ? "checked disabled" : "" }}>
                                    Tidak
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="alreadytravel-date mt-2" style="{{ !empty($data['alreadyTrav']) && $data['alreadyTrav'] == 'yes' ? '' : 'display: none;' }}">
                            <div class="form-group pt-0">
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control datepicker bg-section  type-1 pl-4" name="depature_date" id="inputdepartureDate"
                                     value="{{ $data['depature_date'] }}"   placeholder="TANGGAL BERANGKAT" />
                                </div>
                            </div>

                        </div>
                        <div class="formDestination mt-5">
                            <div class="mt-2 divTypePlan">
                                <label class="text-dark text-md-left">TIPE PAKET</label>
                                <select class="selectpicker w-100 pb-5 text-dark typePlan bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="package_type" title="TIPE PAKET"
                                    data-size="7" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type['ID'] }}" {{ $data['package_type'] == $type['ID'] ? "selected" : '' }}>{{ $type['Name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-2">
                                <label for="startDate" class="text-dark text-md-left">TANGGAL PERGI</label>
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="startDate"
                                        value="{{ $data['start_date'] }}" placeholder="TANGGAL PERGI" >
                                </div>
                            </div>
                            <div class="mt-2" >
                                <label for="endDate" class="text-dark text-md-left">TANGGAL PULANG</label>
                                <div class="input-group has-primary">
                                    <input type="text" class="form-control type-1 datepicker pl-4 endDate  bg-section" id="endDate"
                                        value="{{ $data['end_date'] }}" placeholder="TANGGAL PULANG" />
                                </div>
                            </div>
                            <div class="mt-2">
                                <label for="days" class="text-dark text-md-left">HARI</label>
                                <input type="number" class="form-control type-1 pl-4 days text-dark bg-section" name="days" min="1" id="days"
                                    value="{{ $data['days'] }}" placeholder="Hari" readonly>
                            </div>
                            <div class="mt-3 divTypeProduct">
                                <label class="text-dark text-md-left">TIPE PRODUK</label>
                                <select class="selectpicker w-100 pb-5 text-dark typeProduct bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="product_type" title="TIPE PRODUK"
                                    data-size="7" required>
                                        <option value="Individual" {{ $data['product_type'] == "Individual" ? 'selected' : '' }}>INDIVIDU</option>
                                        <option value="Family" {{ $data['product_type'] == "Family" ? 'selected' : '' }}>KELUARGA</option>
                                        <option value="Duo Plus" {{ $data['product_type'] == "Duo Plus" ? 'selected' : '' }}>DUO PLUS</option>
                                </select>
                            </div>
                            <div class="mt-3 ">
                                <label for="travelneeds" class="text-dark text-md-left">KEPERLUAN PERJALANAN</label>
                                <select id="travelneeds" class="selectpicker w-100 pb-5 text-dark travelneeds bg-section type-1" data-live-search="true"
                                    data-style="select-with-transition" name="travel_need" title="KEPERLUAN PERJALANAN"
                                    data-size="7" required>
                                    @foreach ($travelneeds as $travelneed)
                                        <option value="{{ $travelneed['ID'] }}" {{ $data['travel_need'] == $travelneed['ID'] ? 'selected' : '' }}>{{ $travelneed['Name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-5">
                                <div class="col-sm-12 text-center">
                                    <button class="btn btn-info-2 btn-round btn-sm-block bg-primary-2 btn-submit-section">Lihat produk</button>
                                </div>
                            </div>
                        </div>
                        <div class="productResult mt-5 pt-4">
                             <div class="sub-judul mb-1 bg-section shadow-none">
                                <div class="font-weight-normal pt-2 pl-2 text-left">
                                    <p class="p-text">
                                        PILIHAN PRODUK
                                    </p>
                                </div>
                            </div>
                            <div class="container">
                                <input type="hidden" id="zurich_product_id" name="zurich_product_id" value="{{ $data['zurich_product_id'] }}">
                                <input type="hidden" id="zurich_plan_id" name="zurich_plan_id" value="{{ $data['zurich_plan_id'] }}">
                                <input type="hidden" id="zurich_product_name" name="zurich_product_name" value="{{ !empty($data['zurich_product_name']) ? $data['zurich_product_name'] : '' }}">
                                <div class="row mb-3">
                                    <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-loop-0">
                                        <div class="card">
                                            <div class="card-body ">
                                                <div class="text-center">
                                                    <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                        <b style="color:#263570;" class="">{{ $data['zproduct']['TravellerTypeName'].' - '.$data['zproduct']['PlanName'] }}</b>
                                                    </span>
                                                </div>
                                                <div class="text-left pt-3">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <p><b>Manfaat</b>: </p>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <ul class="nav flex-column nav-cover-product-first">
                                                                @php
                                                                    $last = [];
                                                                @endphp

                                                                @foreach ($data['zproduct']['coverages'] as $key => $coverage)
                                                                    @if($key < 3)
                                                                        <li class="nav-item mt-2">
                                                                            <b>{{ $coverage->name }}</b> <br>
                                                                            {{ $coverage->description }}
                                                                        </li>
                                                                    @elseif($key > 3)
                                                                        @php
                                                                            $last[] = $coverage;
                                                                        @endphp
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="container text-center">
                                                    <h4 class="h-1 text-primary">
                                                        <b style="color:#263570">Rp {{ number_format($data['zproduct']['MainRate'],0, ',' , '.') }}</b>
                                                    </h4>
                                                </div>
                                                <div class="d-flex justify-content-between w-100 mt-3">
                                                    <a href="javascript:;" class="btn btn-white btn-round btn-block ml-2 btn-coverage des_product"
                                                        style="border: 2px solid #263570;" data-key="first" data-last="{{ json_encode($last,true) }}">
                                                        <span class="media-heading font-weight-bold">
                                                            <b style="color:#263570" class="">Deskripsi</b>
                                                        </span>
                                                    </a>
        
                                                    <a href="javascript:;"
                                                        class="btn bg-primary-4 btn-round nav-select-product button-clr-0 ml-2 btn-block"
                                                        data-ids="{{ $data['zproduct']['ID'] }}" data-product="{{json_encode($data['zproduct'],true)}}" data-keys="0" data-planIds="{{ $data['zproduct']['PlanID'] }}">
                                                        <span class="media-heading font-weight-bold button-select-0 button-select">
                                                            <b>Terpilih</b>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="additional_coverage mt-3">
                             <div class="sub-judul bg-section mb-1 shadow-none">
                                <div class="font-weight-normal pt-2 pl-2 text-left">
                                    <p class="p-text">
                                    PERLINDUNGAN TAMBAHAN (OPTIONAL)
                                    </p>
                                </div>
                            </div>
                            @foreach ($data['zurich_coverages'] as $keyCover => $coverage)
                                <div class="container sub-judul mb-1 shadow-none mt-3">
                                    <div class="d-flex justify-content-between py-2">
                                        <p>{{ $coverage['Name'] }}</p>
                                        <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input input-{{ $coverage['ID'] }} optional" type="checkbox" value="{{ $coverage['ID'] }}" name="additional_coverage[]" 
                                                data-opsi="{{ json_encode($coverage,true) }}" {{ $data['coverages'][$keyCover] == $coverage['ID'] ? 'checked' : '' }}>
                                            &nbsp;
                                            <span class="form-check-sign">
                                                <span class="check"></span>
                                            </span>
                                        </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="container">
                                <div class="row row-add-product">
                                    @foreach ($data['zurich_coverages'] as $keyCover => $coverage)
                                        <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-add-loop">
                                            <div class="card">
                                                <div class="card-body ">
                                                    <div class="text-center">
                                                        <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                            <b style="color:#263570;" class="add_name_card-">{{ $coverage['Name'] }}</b>
                                                        </span>
                                                    </div>
                                                    <div class="text-left pt-3">
                                                        <div class="row">
                                                            <div class="col-md-12 mx-auto text-center">
                                                                <img src="{{ asset('assets/travel/img/product/checkup.png') }}" alt="ig" class="img-fluid">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 des_add_cover">
                                                            <p>{{ $coverage['Description'] }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="container text-center">
                                                        <h4 class="h-1 text-primary">
                                                            <b style="color:#263570" class="add_pax_price">Rp {{ number_format($coverage['MainRate'],0, ',' , '.') }}</b>
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form__summary">
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
                                                                {{-- $travel_product['MainRate'] --}}
                                                                <span id="sum-product-pax" price="{{$data['zproduct']['MainRate']}}" style="font-size: 13px;">
                                                                    Rp {{ number_format($data['zproduct']['MainRate'],0, ',' , '.') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                            @php
                                                $total_cover = $data['zproduct']['MainRate'];
                                            @endphp
                                            <div class="table_sum">
                                                <div class="col-md-12 mt-2">
                                                    @foreach ($data['zurich_coverages'] as $keyCover => $coverage)
                                                        @if($data['coverages'][$keyCover] == $coverage['ID'])
                                                            @php
                                                                $total_cover = $total_cover + $coverage['MainRate'];
                                                            @endphp
                                                            <span class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                                                                Perlindungan Tambahan
                                                            </span>
                                                            <span style="color:#263570; font-weight: bold; font-size: 16px">
                                                                {{ $coverage['Name'] }}
                                                            </span>
                                                            <table class="table table-borderless mt-2">
                                                                <thead>
                                                                    <tr style="">
                                                                        <td scope="col" style="color:#263570; width: 250px;">
                                                                            <u style="font-size: 15px;font-weight: bold;">Kelompok Umur</u>
                                                                            <br>
                                                                            <span style="font-size: 13px;"> +- 70 Tahun </span>
                                                                        </td>
                                                                        <td scope="col" style="color:#263570; font-weight: bold; font-size: 15px; width: 250px;">
                                                                            <u>Jumlah Peserta</u>
                                                                            <br>
                                                                            <span style="font-size: 13px;"> 1 </span>
                                                                        </td>
                                                                        <td scope="col" style="color:#0CBC8B; font-weight: bold; font-size: 15px;">
                                                                            <u>Premi</u>
                                                                            <br>
                                                                            <span data-price="{{ $coverage['MainRate'] }}" style="font-size: 13px;"> Rp {{ number_format($coverage['MainRate'],0, ',' , '.') }} </span>
                                                                        </td>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-4">
                                                 <div class="sub-judul bg-section mb-1 shadow-none">
                                                    <div class="font-weight-normal pt-2 pl-3 pr-3 text-left">
                                                        <div class="d-flex justify-content-between">
                                                            <p class="p-text" style="color:#0CBC8B; font-weight: bold;">
                                                                Total Biaya Perlindungan
                                                            </p>
                                                            <p class="p-text" id="total-price" style="color:#0CBC8B; font-weight: bold;">
                                                                Rp {{ number_format($total_cover,0, ',' , '.') }}
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
                    {{-- PENUTUPAN --}}
                    @if(!empty($data['penutupan']) && count($data['penutupan']) > 0)
                        <div class="productResult mt-3 pt-4">
                            <div class="sub-judul mb-1 shadow-none">
                                <div class="font-weight-normal pt-2 pl-2 text-left">
                                    <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                        <b style="color:#263570;" class="">DATA PENUTUPAN</b>
                                    </span>
                                </div>
                            </div>
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-loop-0">
                                        <div class="card">
                                            <div class="card-body ">
                                                <div class="text-left pt-3">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <ul class="nav flex-column nav-cover-product-first">
                                                                @foreach ($data['penutupan'] as $keyPenutupan => $penutupan)
                                                                    @if($keyPenutupan == 0) 
                                                                        <li class="nav-item mb-4">
                                                                            <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                                                <b style="color:#263570;" class="">Pemegang Polis</b>
                                                                            </span>
                                                                        </li>
                                                                    @elseif($keyPenutupan == 1)
                                                                        <li class="nav-item mt-4 mb-4">
                                                                            <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                                                <b style="color:#263570;" class="">Data Pasangan/Anak</b>
                                                                            </span>
                                                                        </li>
                                                                    @else
                                                                        <li class="nav-item mt-4 mb-4">
                                                                            <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                                                <b style="color:#263570;" class="">Data Anak</b>
                                                                            </span>
                                                                        </li>
                                                                    @endif
                                                                        
                                                                    <li class="nav-item mt-2">
                                                                        <b>TITLE</b> <br>
                                                                        {{ strtoupper($penutupan['insured_title']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>NAMA DEPAN</b> <br>
                                                                        {{ strtoupper($penutupan['insured_firstname']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>NAMA BELAKANG</b> <br>
                                                                        {{ strtoupper($penutupan['insured_lastname']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>TEMPAT LAHIR</b> <br>
                                                                        {{ strtoupper($penutupan['insured_birthplace']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>TANGGAL LAHIR</b> <br>
                                                                        {{ strtoupper($penutupan['insured_dob']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>PILIHAN IDENTITAS</b> <br>
                                                                        {{ strtoupper($penutupan['insured_identity']) }}
                                                                    </li>
                                                                    <li class="nav-item mt-2">
                                                                        <b>NO IDENTITAS</b> <br>
                                                                        {{ strtoupper($penutupan['insured_noindentity']) }}
                                                                    </li>
                                                                    @if($keyPenutupan == 0)
                                                                        <li class="nav-item mt-2">
                                                                            <b>NO HANDPHONE</b> <br>
                                                                            {{ strtoupper($penutupan['insured_phone']) }}
                                                                        </li>
                                                                        <li class="nav-item mt-2">
                                                                            <b>EMAIL</b> <br>
                                                                            {{ strtoupper($penutupan['insured_email']) }}
                                                                        </li>
                                                                        <li class="nav-item mt-2">
                                                                            <b>KOTA TEMPAT TINGGAL</b> <br>
                                                                            {{ strtoupper($penutupan['insured_origin']) }}
                                                                        </li>
                                                                        <li class="nav-item mt-2">
                                                                            <b>KODE POS</b> <br>
                                                                            {{ strtoupper($penutupan['insured_poscode']) }}
                                                                        </li>
                                                                        <li class="nav-item mt-2">
                                                                            <b>ALAMAT</b> <br>
                                                                            {{ strtoupper($penutupan['insured_alamat']) }}
                                                                        </li>
                                                                    @else
                                                                        <li class="nav-item mt-2">
                                                                            <b>ALAMAT</b> <br>
                                                                            {{ strtoupper($penutupan['insured_alamat']) }}
                                                                        </li>
                                                                        <li class="nav-item mt-2">
                                                                            <b>HUBUNGAN DENGAN PEMEGANG POLIS</b> <br>
                                                                            {{ strtoupper($penutupan['insured_relationship']) }}
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="mt-5">
                        <div class="row">
                            <div class="col-6 text-center mx-auto">
                                <button class="btn btn-info-2 btn-round bg-primary-2 btn-penutupan btn-block">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPenawaran" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalPenawaranLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="FormPenawaran">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container text-center">
                            <h3 class="title" style="font-size: 14px;"> Silahkan isi data penerima penawaran </h3>
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
    @if(!empty($data['alreadyTrav']) && $data['alreadyTrav'] == "yes") 
        <script>
            document.getElementById("alreadyTravYes").checked = true;
        </script>
    @else
        <script>
            document.getElementById("alreadyTravNo").checked = true;
        </script>
    @endif
    
    @if(!empty(\Request::get('type')) && \Request::get('type') == 'readonly')
    <script>
        const trv_1 = document.getElementById('alreadyTravYes');
        const trv_2 = document.getElementById('alreadyTravNo');
        trv_1.setAttribute('disabled', true);
        trv_2.setAttribute('disabled', true);
        $(function () {
            $('body').find('input').attr('readonly', true);
            $('body').find('select').attr('disabled', true);
            $('body').find('input').attr('disabled', true);
            $('body').find('input').prop('disabled', true);
            $('body').find('button, a').attr('disabled', true);
            $('#alreadyTravYes').attr('disabled', true);
            $('#alreadyTravYes').attr('disabled', true);
            $('#alreadyTravNo').attr('disabled', true);
            $('#alreadyTravYes').attr('new-data')
        })
    </script>
    @endif
    
    @include('travel.penawaran.script')

</body>

</html>
