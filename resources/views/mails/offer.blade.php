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
      /* float: left; */
      margin-top: 8px;
    }

    /* #logo img {
      height: 70px;
    } */

    #company {
      /*float: right;*/
      text-align: right;
    }


    #details {
      margin-bottom: 50px;
    }

    #client {
      padding-left: 6px;

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
    }

    table tfoot tr:first-child td {
      border-top: none;

    }

    table tfoot tr:last-child td {
      color: #ffffff;
      background-color: #ffffff;
      /*font-size: 1.5em;*/
      /* border-top: 1px solid #57B223; */

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
    .indent{
        text-indent: 20px;
    }
    /* .size-mg{
      width:200px !important; 
      height: 50px !important;
    } */
    /* .size-mg2{
      width:150px !important; 
      height: 40px !important;
    } */
  </style>
</head>

<body>
  <div class="container">
    <table style="border: 5px;  border-style: none; border-style: hidden;">
      {{-- <thead>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align: left;" >
            <img src='{{asset('/assets/img/zurichnew.png')}}' style="text-align:left; left:0px; margin-left:-100px; width:200px; height: 50px;">
                  <img src='https://salvus.co.id/assets/images/logosalvus.png' style="text-align:left; margin-bottom:10px; padding-left:10px; width:150px; height: 40px;">
              <div id="" style="text-align:left; left:0px; padding:0px; margin-left:-100px;">
                  
              </div> 
          </th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
      </thead> --}}
      <tr>
        <th style=" text-align:left;" >
          <img src='{{asset('/assets/img/zurich_salvus.png')}}' style="text-align:left; left:0px;" class="size-mg" >
                    {{-- <img src='{{asset('/assets/img/salvusmail.png')}}' style="text-align:left; margin-bottom:10px; padding-left:10px;" class="size-mg2"> --}}
        </th>
      </tr>
      <tbody style="font-family: Calibri, sans-serif; font-size:14px;">
          
        <tr>
          <td colspan="" style="font-family: Calibri, sans-serif; font-size:14px;">
            <p>Nasabah yang terhormat</p>
            {{-- <p>Berikut kami lampirkan penawaran untuk produk asuransi [NAMA PRODUK] </p> --}}
            <p>Berikut kami lampirkan penawaran untuk produk asuransi {{ $data['name'] }} </p>
            <p>Silahkan membaca dokumen ini dengan hati-hati untuk memastikan bahwa manfaat yang diperoleh memenuhi kebutuhan Anda.
            Jika tidak, silahkan menghubungi pialang atau agen asuransi Anda. Perlindungan hanya berlaku setelah PT Zurich Asuransi
            Indonesia Tbk atau perwakilannya yang sah menerima pembayaran premi.</p>
            <br>
            <p>Pesan ini dibuat secara otomatis, mohon tidak membalas. Jika butuh bantuan, silakan hubungi PT Salvus Inti melalui
            telepon +6221 569 53505.</p>
            <br>
            {{-- <p>
              Perusahaan Asuransi: <br>
              <b>PT Zurich Asuransi Indonesia Tbk</b> <br>
              Graha Zurich - Jl. Letjen M.T. Haryono Kav.42, Jakarta Selatan 12780, Indonesia
            </p>
            <br>
            <p>
              Hubungi Kami  <br>
              Zurich Care 1500 456 <br>
              zurichcare.general@zurich.co.id <br>
              www.zurich.co.id
            </p> --}}
            {{-- <p style="font-style: italic;">
              PT Zurich Asuransi Indonesia Tbk merupakan asuransi umum yang terdaftar dan diawasi oleh Otoritas Jasa Keuangan.
            </p> --}}
            <br>
            <p>
              Perusahaan Pialang: <br>
              <b> PT Salvus Inti </b> <br>
              Jl. Tomang Raya No. 47F, Jakarta 11440, Indonesia 
            </p>
            <br>
            <p>
              Informasi Kontak <br>
              Email kami : cs@salvus.co.id <br>
              Layanan pelanggan : +62 21 569 53 505 
            </p>
            <p style="font-style: italic;">
              PT Salvus Inti merupakan pialang asuransi berlisensi yang terdaftar dan diawasi oleh Otoritas Jasa Keuangan Indonesia. PT Zurich Asuransi Indonesia Tbk merupakan asuransi umum yang berizin dan diawasi oleh Otoritas Jasa Keuangan Indonesia
            </p>
          </td>
        </tr>
        
      </tbody>

     
      </table>
  </div>
</body>

</html>