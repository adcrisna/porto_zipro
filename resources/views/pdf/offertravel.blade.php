<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Surat Penawaran Asuransi Kendaraan</title>
</head>

<style>

  .layout {
    width: 100%;
  }
 
  body {
    /* font-size: 15px; */
      /*width: 21cm; */
      /*height: 29.7cm; */
      margin: 0 auto;
      font-style:normal;
      font-weight:normal;
      font-size:9.3pt;
      font-family:Cambria Math;
}
  .left{
    font-size: 14px;
    text-decoration: underline;
    font-weight: 900;
  }
  .left-noborder{
    font-size: 14px;
    font-weight: 900;
  }
  .info-title{
    font-size: 24px;
     text-align: center;
     font-weight: 900;
     font-family: Arial, Helvetica, sans-serif;
  }
  .info{
    text-align: center;
  }
  .text-right{
    text-align: right;
  }
  hr {

  }
  
  table.content > tr {
    
  }
  .text-footer{
    text-align: left;
    font-size: 10.5px;
  }
  .footer {

    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}
#watermark {
  position: relative;
  /* height: 50%; */
  /* transform-origin: 50% 50%; */
  z-index: -1000;
}

</style>
<body>
  <div id="watermark">
    <img src='{{$base64}}' style="position: absolute; left: -6.89%; right:0; opacity: .2;" alt="BG" height="104.8%" width="110%">
  </div>
  <div class="container">
  <section class="layout">
    <div class="header">
      <table class="layout" >
        <tbody>
          <tr>
            <td>
              <img src='{{public_path()."/assets/img/zipro.png"}}' style="float: right" height="110px">
              <img src='{{public_path()."/assets/img/logobaru.png"}}' height="110px">
            </td>
          </tr>
        </tbody>
      </table>
       
      <p style="font-size: 18pt; font-weight: bold;">Penawaran Asuransi</p>
      <hr style="margin-top: -15px;border: 0.5px solid ;">
    </div>
  </section>
  <section class="layout">
    <table class="layout">
        <thead>
          <tr>
            <th style="width: 200px;" ></th>
            <th style="width: 300px;" ></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Nomor</td>
                <td>QS-ZIPRO00{{ $order->id }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Nama Tertanggung</td>
                <td>{{ $order->offering_name ?? "" }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Email Tertanggung</td>
                <td>{{ $order->offering_email ?? "" }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Nomor Tertanggung</td>
                <td>{{ $order->offering_telp ?? "" }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Tanggal</td>
                <td>{{ $order->created_at }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Nama Penanggung</td>
                <td>PT Zurich Asuransi Indonesia, Tbk</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Nama Produk</td>
                <td>Travellin </td>
                <td></td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; padding:5px; ">Jenis Perlindungan</td>
                <td>{{ $order->additional_data[0]['product_type'] }}</td>
                <td></td>
            </tr>
            <tr>
                <td  style="text-decoration: underline; font-weight:100; padding:5px;  vertical-align: top;" rowspan="6">Objek Pertanggungan</td>
                <td>Area Tujuan</td>
                <td>: {{ $tujuan }}</td>
            </tr>
            <tr>
                <td>Tipe Plan</td>
                <td>: {{ $travel_product['PlanName'] ?? '-'}}</td>
            </tr>
            <tr>
                <td>Jumlah Hari</td>
                <td>: {{ $order->additional_data[0]['days'] }} hari</td>
            </tr>
            <tr>
                <td>Negara Tujuan</td>
                <td>: {{ $region_selected }}</td>
            </tr>
            <tr>
                <td>Tipe Paket</td>
                <td>: {{ $types['Name'] }}</td>
            </tr>
            <tr>
                <td>Tipe Produk</td>
                <td>: {{ $order->additional_data[0]['product_type'] }}</td>
            </tr>
            <tr>
                <td>Keperluan Perjalanan</td>
                <td>: {{ $travelneeds['Name'] }}</td>
            </tr>
            <tr>
                <td style="text-decoration: underline; font-weight:100; vertical-align: top; margin-top: 30px;" rowspan="12">Premi Asuransi</td>
                {{-- <td style="font-weight:100; padding:5px;">Jenis Perluasan</td>
                <td style="font-weight:100; padding:5px;">Premi </td> --}}
            </tr>
          
        </tbody>
    </table>
    <hr style="border: 0.5px solid ;">
    <table class="layout" >
      <thead>
        <tr>
          <th style="width: 200px;" ></th>
          <th style="width: 300px;" ></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="text-decoration: underline; font-weight:100; padding:5px;">Total Premi Asuransi  </td>
          <td></td>
          <td>Rp {{ number_format($order->base_price,0, ',' , '.') }}</td>
        </tr>
        @if (!empty($order->deduct))
        <tr>
          <td style="text-decoration: underline; font-weight:100; padding:5px;">Diskon </td>
          <td></td>
          <td>Rp {{ number_format($order->base_price * $order->deduct / 100,0, ',' , '.') }}</td>
        </tr>
        @endif
        <tr>
          <td style="text-decoration: underline; font-weight:100; padding:5px;">Biaya Administrasi</td>
          <td></td>
          <td>Rp 10.000</td>
        </tr>
        <tr>
          <td style="text-decoration: underline; font-weight:100; padding:5px;">Premi yang Harus Dibayarkan</td>
          <td></td>
          <td>Rp {{ number_format($order->total,0, ',' , '.') }}</td>
        </tr>
      </tbody>
    </table>
  </section>
  <div class="footer">
    <table>
      <tr>
        <td>
          <p class="text-footer" style="color: #104F9F ">
            <b>PT Salvus Inti </b>
            <br> 
            Jl. Tomang Raya No. 47F
            <br> 
            Jakarta 11440, Indonesia
            <br>
            Claim Center 24 Jam ( Whatsapp): +62 821 1335 3479
            <br>
            Call Center: +62 21 569 53 505
            <br>
            Office: +62 21 566 6909


          </p>
        </td>
      </tr>
    </table>
  </div>
  <div style="page-break-before: always"></div>
  <div id="watermark">
    <img src='{{$base64}}' style="position: absolute; left: -6.89%; right:0; opacity: .2;" alt="BG" height="104.8%" width="110%">
  </div>
  <section class="layout" >
    <p class="info-title">Pemberitahuan Penting</p>
      <p class="info">
        <ol style="font-size: 16px; font-style: italic; font-family: Arial, Helvetica, sans-serif">
          <li>Silahkan membaca dokumen ini dengan hati-hati untuk memastikan bahwa manfaat yang diperoleh
            memenuhi kebutuhan anda. Jika tidak, silahkan menghubungi pialang atau agen asuransi anda atau
            menghubungi Zurich care di 1500 456</li>
          <li>Polis hanya berlaku setelah PT Zurich Asuransi Indonesia Tbk atau perwakilannya yang sah
            menerima pembayaran premi.</li>
          <li>
            Total premi yang tercantum belum termasuk biaya transaksi
          </li>
        </ol>
      </p>
  </section>
  <div style="text-align: center; margin-top: 60px">
    <p style="font-size: 13px;">
      PT Salvus Inti merupakan pialang asuransi berlisensi yang terdaftar dan diawasi oleh Otoritas Jasa Keuangan Indonesia
      <br>
      PT Zurich Asuransi Indonesia Tbk merupakan asuransi umum yang berizin dan diawasi oleh Otoritas Jasa Keuangan Indonesia
    </p>
  </div>
  <div class="footer">
    <table>
      <tr>
        <td>
          <p class="text-footer" style="color: #104F9F ">
            <b>PT Salvus Inti </b>
            <br> 
            Jl. Tomang Raya No. 47F
            <br> 
            Jakarta 11440, Indonesia
            <br>
            Claim Center 24 Jam ( Whatsapp): +62 821 1335 3479
            <br>
            Call Center: +62 21 569 53 505
            <br>
            Office: +62 21 566 6909


          </p>
        </td>
      </tr>
    </table>
  </div>
</div>
</body>
</html>