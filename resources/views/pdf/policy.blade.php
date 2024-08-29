<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Ringkasan Polis</title>
  <style account="text/css">
    @font-face {
      font-family: SourceSansPro;
      src: url(SourceSansPro-Regular.ttf);
    }

    .watermark {
      position: fixed;
      bottom: 0px;
      margin-left: -45px;
      margin-bottom: -45px;
      right: 0px;
      width: 1130px;
      height: 1500px;
      z-index: -1;
    }

    .container {
      padding: 0.01em 100px;
    }

    .clearfix:after {
      content: "";
      display: table;
      clear: both;
    }

    a {
      color: #0087C3;
      text-decoration: none;
    }

    body {
      position: relative;
      /*width: 21cm;  */
      /*height: 29.7cm; */
      margin: 0 auto;
      color: black;
      background: #FFFFFF;
      font-family: Arial, sans-serif;
      font-size: 14px;
      /* font-family: SourceSansPro; */
    }

    header {
      padding: 10px 0;
      margin-bottom: 20px;
    }

    #logo {
      float: left;
      margin-top: 8px;
    }

    #logo img {
      height: 70px;
    }

    #company {
      /*float: right;*/
      text-align: right;
    }


    #details {
      margin-bottom: 50px;
    }

    #client {
      padding-left: 6px;
      border-left: 6px solid #0087C3;
      float: left;
    }

    #client .to {
      color: #777777;
    }

    h2.name {
      font-size: 1.4em;
      font-weight: normal;
      margin: 0;
    }

    #invoice {
      /*float: right;*/
      text-align: right;
    }

    #invoice h1 {
      color: #0087C3;
      font-size: 2.0em;
      line-height: 1em;
      font-weight: normal;
      margin: 0 0 10px 0;
    }

    #invoice .date {
      font-size: 1.1em;
      color: #777777;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
      margin-bottom: 20px;
    }

    table th,
    table td {
      padding: 20px;
      /* background: #EEEEEE; */
      text-align: center;
      border-bottom: 1px solid #FFFFFF;
    }

    table th {
      white-space: nowrap;
      font-weight: normal;
    }

    table td {
      text-align: left;
    }

    table td h3 {
      color: #57B223;
      font-size: 1.2em;
      font-weight: normal;
      margin: 0 0 0.2em 0;
    }

    table .no {
      color: #FFFFFF;
      font-size: 1.6em;
      background: #57B223;
    }

    table .desc {
      text-align: left;
    }

    table .unit {
      background: #DDDDDD;
    }

    table .qty {}

    table .total {
      background: #21224E;
      color: #FFFFFF;
    }

    table td.unit,
    table td.qty,
    table td.total {
      font-size: 1.2em;
    }

    table tbody tr:last-child td {
      border: none;
    }

    table tfoot td {
      padding: 10px 20px;
      background: #FFFFFF;
      border-bottom: none;
      font-size: 1.2em;
      white-space: nowrap;
      border-top: 1px solid #AAAAAA;
    }

    table tfoot tr:first-child td {
      border-top: none;
    }

    table tfoot tr:last-child td {
      color: #57B223;
      /*font-size: 1.5em;*/
      border-top: 1px solid #57B223;

    }

    table tfoot tr td:first-child {
      border: none;
    }

    #thanks {
      font-size: 2em;
      margin-bottom: 50px;
    }

    #notices {
      padding-left: 6px;
      border-left: 6px solid #0087C3;
    }

    #notices .notice {
      font-size: 1.2em;
    }

    footer {

      width: 100%;
      height: 30px;
      position: relative;
      top: 80px;
      padding: 8px 0;
      text-align: center;
    }

    .info {
      padding: 10px !important;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="watermark"><img src='{{public_path()."/assets/img/sprint-background2.jpg"}}' height="100%" width="100%">
    </div>
    <header class="clearfix">
      <div id="logo">
        <img src='{{public_path()."/assets/img/salvus.jpg"}}'>
      </div>
      <div id="company">
        <h2 class="name">PT Salvus Inti</h2>
        <div>No.47F, Jl. Tomang Raya, RT.1/RW.3,</div>
        <div>Tomang, Grogol petamburan, West Jakarta City,</div>
        <div>Jakarta 11440</div>
      </div>
    </header>
    @php
    $polis = $data['policy']['contract-wording'];
    @endphp
    <main>
      <center>
        <h2 style="font-weight: lighter;">ASURANSI {{ $data['policy']['binder_name'] }}</h2>
        <h2 style="font-weight: lighter;">RINGKASAN POLIS</h2>

      </center>
      <table border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="total" colspan="4">INFORMASI PEMEGANG RINGKASAN POLIS
            </th>
          </tr>
        </thead>
        <tbody>
            <tr>
              <td class="info" style="width:13%"></td>
                <td class="info">Nomor Ringkasan Polis</td>
                :
                <td class="info">: {{ $polis['policy_number'] }}</td>
                <td class="info" style="width:13%"></td>
            </tr>
          	@foreach ($orders->data as $key=>$data)
              @if ($data['type'] != "images" && $data['type'] != "radio")

                        @php
                         if($key == "37"){
                           $data['data'] = $data['data'] ? "Pria":"Wanita";
                         }
                         if($data['type'] == "date"){
                           $data['data'] = date("d-m-Y" , strtotime($data['data']));
                         }
                        @endphp
                <tr>
                  <td class="info" style="width:13%"></td>
                    <td class="info">{{ $data['name'] }}</td>
                    {{-- {{ getRepoType($key)->name }} --}}
                    :
                    {{-- {{ $data['type'] }} --}}
                    <td class="info">: {{ $data['data'] }}</td>
                    
                    {{-- @if ($data['type'] == "drop" || $data['type'] == "radio")
                      {{ getArrayOpt(getRepoType($key)->value)->value[$data['data']] }}
                    @else
                      {{ !empty($data['data']) ? $data['data'] : '-' }}

                    @endif --}}
                    <td class="info" style="width:13%"></td>
                </tr>
              @endif
            @endforeach
            <tr>
              <td class="info" style="width:13%"></td>
              @php
              $startPeriode = isset($orders->transaction->policy_start) ? date_create_from_format("Y-m-d",$orders->transaction->policy_start) :
              null;
              $endPeriode = isset($orders->transaction->policy_end) ? date_create_from_format("Y-m-d",$orders->transaction->policy_end) :
              null;
              @endphp
              <td class="info">Periode Polis <I>(DD/MM/YYYY)</I></td>
              <td class="info">: {{ isset($orders->transaction->policy_start) ? date_format($startPeriode,"d/m/Y") : null }} –
                {{ isset($orders->transaction->policy_end) ? date_format($endPeriode,"d/m/Y") : null }}</td>
  
              <td class="info" style="width:13%"></td>
            </tr>
          {{-- <tr>
            <td class="info" style="width:13%"></td>
            <td class="info">Nomor Ringkasan Polis <I>Policy Summary Number</I></td>
            <td class="info">: {{ $polis['policy_number'] }}</td>
            <td class="info" style="width:13%"></td>

          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Nama Asuransi <I>Insurer Name</I></td>
            <td class="info">: {{ $data['product_name'] }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Nama Tertanggung<I> Name of Insured</I></td>
            <td class="info">: {{ $data['name'] ? $data['name']:$polis['client_name'] }}</td>
            <td class="info" style="width:13%"></td>
          </tr> --}}
          {{-- <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Alamat Tertanggung <I>Address of Insured</I></td>
            <td class="info">: {!! $data['alamat'] !!}</td>

            <td class="info" style="width:13%"></td>
          </tr> --}}
          {{-- <tr>
            <td class="info" style="width:13%"></td>
            @php
            $startPeriode = isset($polis['periode_start']) ? date_create_from_format("d M Y",$polis['periode_start']) :
            null;
            $endPeriode = isset($polis['periode_start']) ? date_create_from_format("d M Y",$polis['periode_end']) :
            null;
            @endphp
            <td class="info">Periode Polis <I>Policy Period (DD/MM/YYYY)</I></td>
            <td class="info">: {{ isset($polis['periode_start']) ? date_format($startPeriode,"d/m/Y") : null }} –
              {{ isset($polis['periode_end']) ? date_format($endPeriode,"d/m/Y") : null }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Periode Diterbitkan <I>Policy Issues (DD/MM/YYYY)</I></td>
            <td class="info">: {{ isset($polis['periode_start']) ? date_format($startPeriode,"d/m/Y") : null }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          @if ($data['type'] == "travel")
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Area <I>Region</I></td>
            <td class="info">: Jakarta</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Tujuan <I>Destination</I></td>
            <td class="info">: Domestik</td>

            <td class="info" style="width:13%"></td>
          </tr>
          @endif
          @if ($data['cats'] == "Gadget")
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Merk Handphone <I>Handphone Brand</I></td>
            <td class="info">: {{ $data['merk'] }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Tipe Handphone <I>Phone Type</I></td>
            <td class="info">: {{ $data['type'] }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Nomor Identitas handphone <I>Phone Identification Number</I></td>
            <td class="info">: {{ $data['imei'] }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          @endif
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Paket Yang Dipilih <I>Selected Package</I></td>
            <td class="info"> : {{ $data['policy']['binder_name'] }} </td>
            <td class="info" style="width:13%"></td>

          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Premi & Biaya Polis <I>Premium & Policy Cost</I></td>
            <td class="info">: Rp {{ cRupiah($data['price']) }}</td>

            <td class="info" style="width:13%"></td>
          </tr>
          <tr>
            <td class="info" style="width:13%"></td>

            <td class="info">Tabel Nama Tertanggung <I>Name Table Insured</I></td>
            <td class="info">:</td>

            <td class="info" style="width:13%"></td>
          </tr> --}}
        </tbody>
      </table>
      <table style="border: 1px solid black" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th style="border: 1px solid black" class="total">Nama tertanggung <br> <I>Insured Name</I></th>
            <th style="border: 1px solid black" class="total">Tanggal Lahir <br> <I>Date of Birth</I></th>
            {{-- <th style="border: 1px solid black" class="total">ID No. (Passport/KTP/KITAS)</th> --}}
          </tr>
        </thead>
        <tbody>
          @php
          $startPeriode = isset($polis['periode_start']) ? date_create_from_format("d M Y",$polis['periode_start']) :
          null;
          $endPeriode = isset($polis['periode_start']) ? date_create_from_format("d M Y",$polis['periode_end']) :
          null;
          @endphp
          <tr>
            <td style="border: 1px solid black; text-align:center">
              {{ $orders->data[1]['data'] }} 
            </td>

            @php
            $dob = "-";
            if(isset($orders->data[5]['data'])){
              $dob = date("d/m/Y", strtotime(str_replace(',','',$orders->data[5]['data'])));
            }
            @endphp
            <td style="border: 1px solid black; text-align:center">
              {{$dob}} 
            </td>
            {{-- <td style="border: 1px solid black; text-align:center"> {{ $data['ktp'] }} </td> --}}

          </tr>
        </tbody>

      </table>
      <p style="text-align:right">
        Jakarta, {{ isset($startPeriode) ? date_format($startPeriode,"d F Y") : null }}
      </p>
      {{-- <div style="justify-content: center;">
          <table style=" width:100%; " cellspacing="0" cellpadding="0">
              <tbody>
                      <tr>
                          <td style="border: none !important;" > </td>
                          <td style="border: 1px solid black; text-align:center; width:25%" >Bea Materai Lunas Rp 6.000 </td>
                          <td style="" > </td>
  
                      </tr>
                      <tr>
                          <td style="" > </td>
                          <td style="border: 1px solid black; text-align:center; width:25%" >Stamp Duty Paid IDR 6.000 </td>
                          <td style="" > </td>
  
                      </tr>
              </tbody>
              
          </table>
        </div>

      <p>
            Catatan penting: {!! $polis['clause'] !!}<br>
        </p> --}}
    </main>
    <div style="position:absolute;bottom:200px;">
      <p style="">
        Lembar ini adalah halaman 1/3
      </p>
    </div>
    <div style="position:absolute;bottom:150px;">
      <p style="font:italic;">
        Disclaimer: Ringkasan polis ini terdiri dari 3 halaman yang tidak dapat dipisahkan

      </p>
    </div>
    <div style="position:absolute;bottom:130px;right:100px;">
      {{-- <img src="{{ url('/e-policy/barcode/'.$data['code'] )}}">  --}}
      {{-- <img src="{{ $qrcode }}">  --}}
    </div>
    <div style="page-break-after: always;height:150px;"></div>
    <div class="watermark"><img src='{{public_path()."/assets/img/sprint-background2.jpg"}}' height="100%" width="100%">
    </div>

    <header class="clearfix">
      <div id="logo">
        <img src='{{public_path()."/assets/img/salvus.jpg"}}'>
      </div>
      <div id="company">
        <h2 class="name">PT Salvus Inti</h2>
        <div>No.47F, Jl. Tomang Raya, RT.1/RW.3,</div>
        <div>Tomang, Grogol petamburan, West Jakarta City,</div>
        <div>Jakarta 11440</div>
      </div>
    </header>
    <main>

      {{-- <h2 style="color:black;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif">
        Identitas
      </h2>
      <table
        style="border: 1px solid black; width:90% height:80%; margin-left:auto; margin-right:auto; margin-bottom:1rem;padding: 0.3rem;"
        cellspacing="0" cellpadding="0">
        <tbody>
          <tr>
            <td style="border: 1px solid black; color:white; background:#21224E; padding: 0.3rem">Nama Tertanggung
              <i>Name of Insured</i> </td>
            <td style="border: 1px solid black; padding: 0.3rem">
              {{ $data['name'] ? $data['name']:$polis['client_name'] }}</td>
          </tr>
          <tr>
            <td style="border: 1px solid black; color:white; background:#21224E; padding: 0.3rem">Tanggal Lahir <i>Date
                of Birth</i> </td>
            <td style="border: 1px solid black; padding: 0.3rem">{{ date("d/m/Y", strtotime(str_replace(',','',$dob)))}}
            </td>
          </tr>
          <tr>
            <td style="border: 1px solid black; color:white; background:#21224E; padding: 0.3rem">ID No.
              (Passport/KTP/KITAS) </td>
            <td style="border: 1px solid black;padding: 0.3rem"> {{ $data['ktp'] }}</td>
          </tr>
          <tr>
            <td style="border: 1px solid black; color:white; background:#21224E; padding: 0.3rem">Nomor Ringkasan Polis
              <i>Policy Summary Number</i></td>
            <td style="border: 1px solid black; padding: 0.3rem">{{ $polis['policy_number'] }}</td>
          </tr>
          <tr>
            <td style="border: 1px solid black; color:white; background:#21224E; padding: 0.3rem">Periode Polis<i>Policy
                Periode (DD/MM/YY)</i></td>
            <td style="border: 1px solid black; padding: 0.3rem">
              {{ isset($polis['periode_start']) ? date_format($startPeriode,"d/m/Y") : null }} –
              {{ isset($polis['periode_start']) ? date_format($endPeriode,"d/m/Y") : null }}</td>
          </tr>
        </tbody> --}}

      </table>

      
      <h2 style="color:black;font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif">
        Manfaat dan Limit Perlindungan
      </h2>
      <table
        style="border: 1px solid black; width:90% height:80%; margin-left:auto; margin-right:auto; margin-bottom:1rem;padding: 0.3rem;"
        cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th style="border: 1px solid black; text-align:center;padding: 0.3rem;">No</th>
            <th style="border: 1px solid black; text-align:center;padding: 0.3rem;">Perihal</th>
            <th style="border: 1px solid black; text-align:center;padding: 0.3rem;">Deskripsi</th>
          </tr>
        </thead>
        <tbody>
          @php
          $i = 1;
          @endphp
          @foreach ($orders->product->form_limit as $limit => $value)
            {{-- @foreach ($index as $n => $c) --}}
            <tr>
              <td style="border: 1px solid black; text-align:center;padding: 0.3rem;"> {{ $i++ }} </td>
              <td style="border: 1px solid black; text-align:center;padding: 0.3rem;"> {{ $value['id'] }} </td>
              <td style="border: 1px solid black; text-align:center;padding: 0.3rem;"> {{ $value['value'] }} </td>
            </tr>
            {{-- @endforeach --}}
          @endforeach

        </tbody>

      </table>


    </main>
      <div style="position:absolute;bottom:200px;">
        <p style="">
          Lembar ini adalah halaman 2/3
        </p>
      </div>
      <div style="position:absolute;bottom:150px;">
        <p style="font:italic;">
          Disclaimer: Ringkasan polis ini terdiri dari 3 halaman yang tidak dapat dipisahkan
        </p>
      </div>
      <div style="position:absolute;bottom:130px;right:100px;">
        {{-- <img src="{{ url('/e-policy/barcode/'.$data['code'] )}}"> --}}
        {{-- <img src="{{ $qrcode }}">  --}}
      </div>

      <div style="page-break-after: always;height:150px;"></div>
      <div class="watermark"><img src='{{public_path()."/assets/img/sprint-background2.jpg"}}' height="100%" width="100%">
      </div>

      <header class="clearfix">
        <div id="logo">
          <img src='{{public_path()."/assets/img/salvus.jpg"}}'>
        </div>
        <div id="company">
          <h2 class="name">PT Salvus Inti</h2>
          <div>No.47F, Jl. Tomang Raya, RT.1/RW.3,</div>
          <div>Tomang, Grogol petamburan, West Jakarta City,</div>
          <div>Jakarta 11440</div>
        </div>
      </header>
      <main>

        <p>
          <i> <b> Catatan penting </b> :</i> <br> <br>
          {!! $polis['clause'] !!}<br>
        </p>

      </main>
      <div style="position:absolute;bottom:400px;">
        <p style="">
          Lembar ini adalah halaman 3/3
        </p>
      </div>
      <div style="position:absolute;bottom:150px;">
        <p style="font:italic;">
          Disclaimer: Ringkasan polis ini terdiri dari 3 halaman yang tidak dapat dipisahkan
        </p>
      </div>
      <div style="position:absolute;bottom:130px;right:100px;">
        {{-- <img src="{{ url('/e-policy/barcode/'.$data['code'] )}}"> --}}
        {{-- <img src="{{ $qrcode }}">  --}}
      </div>
      {{-- <div style="page-break-after: avoid;z-index:-100;"></div> --}}
      {{-- <div class="watermark" style="z-index:-100"><img src='{{public_path()."/assets/img/new-background2.jpg"}}'
      height="100%" width="100%">
  </div> --}}
  </div>
</body>

</html>