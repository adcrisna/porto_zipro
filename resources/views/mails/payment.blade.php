<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Payment Link</title>
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

        .indent {
            text-indent: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <table style="border: 0; border-style: none; border-style: hidden;">
            <tr>
                <th style=" text-align:left;">
                    <img src='{{asset('/assets/img/zurich_salvus.png')}}' style="text-align:left; left:0px;"
                        class="size-mg">

                </th>
            </tr>
            <tbody style="font-family: Calibri, sans-serif; font-size:14px;">

                <tr>
                    <td colspan="" style="font-family: Calibri, sans-serif; font-size:14px;">
                        <p>Nasabah yang terhormat,</p>
                        <p>
                            Terimakasih telah melakukan transaksi melalui platform kami. <br>
                            Mohon segera lakukan pembayaran melalui <a href="{{ $trans_link['pg_link'] }}"
                                style=" font-weight: bold; color:#000000; text-decoration:underline;">tautan</a> ini,
                            paling lambat 3 x 24 jam setelah transaksi berhasil.
                        </p>
                        <br>

                        <p>
                            Dengan menerima pesan ini saya menyatakan bahwa pernyataan yang saya buat dalam pesanan ini,
                            atas sepengetahuan dan keyakinan lengkap serta benar, dan saya dengan ini setuju bahwa data
                            pesanan ini menjadi dasar dan merupakan bagian dari polis yang diterbitkan sehubungan dengan
                            risiko di atas. Disetujui bahwa PT Zurich Asuransi Indonesia Tbk ("Penanggung") bertanggung
                            jawab sesuai dengan ketentuan polis dan bahwa Tertanggung tidak akan mengajukan klaim lain
                            yang bersifat apa pun yang tidak dijamin dalam polis. <br>
                            Dengan melanjutkan ini saya menyatakan bahwa: <br>
                            i. Saya menyetujui bahwa Polis akan disampaikan secara softcopy dan saya akan menghubungi
                            penanggung apabila saya membutuhkan Polis dalam bentuk hardcopy. <br>
                            ii. Telah membaca dan memahami ketentuan produk, ringkasan informasi produk, dan/atau
                            layanan serta hak dan kewajiban didalamnya. <br>
                            iii. Setuju bahwa pengajuan dan pernyataan ini merupakan dasar kontrak antara Penanggung dan
                            saya, dan saya menerima persyaratan, pengecualian, dan ketentuan polis yang akan diterbitkan
                            atau ketentuan tambahan yang dimasukan ke dalamnya. <br>
                            iv. Saya bersedia menerima informasi produk dan/atau layanan dari Penanggung melalui
                            berbagai saluran komunikasi. <br>
                            v. Saya bersedia data saya digunakan untuk keperluan Penanggung dan/atau pihak ketiga yang
                            bekerja sama dengan Penanggung.
                        </p>
                        <br>
                        <p>Pesan ini dibuat secara otomatis, mohon tidak membalas. Jika butuh bantuan, silakan hubungi
                            PT Salvus Inti melalui telepon +6221 569 53505.</p>
                        <br>
                        <br>
                        <p>
                            Perusahaan Asuransi: <br>
                            <b>PT Zurich Asuransi Indonesia Tbk</b> <br>
                            Graha Zurich - Jl. Letjen M.T. Haryono Kav.42, Jakarta Selatan 12780, Indonesia
                        </p>
                        <br>
                        <p>
                            Hubungi Kami <br>
                            Zurich Care 1500 456 <br>
                            zurichcare.general@zurich.co.id <br>
                            www.zurich.co.id
                        </p>
                        <p style="font-style: italic;">
                            PT Zurich Asuransi Indonesia Tbk merupakan asuransi umum yang terdaftar dan diawasi oleh
                            Otoritas Jasa Keuangan.
                        </p>
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
                            PT Salvus Inti adalah pialang asuransi berlisensi yang terdaftar dan diawasi oleh Otoritas
                            Jasa Keuangan.
                        </p>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</body>

</html>