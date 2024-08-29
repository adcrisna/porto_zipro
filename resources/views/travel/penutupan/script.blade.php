<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('select').selectpicker();
    $('#insuredbirth-0').datetimepicker({
        format: 'DD MMM YYYY',
        maxDate: new Date(),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });
    $('#insuredbirth-1').datetimepicker({
        format: 'DD MMM YYYY',
        maxDate: new Date(),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });
    $('#insuredbirth-0').val("");
    $('#insuredbirth-1').val("");
</script>
<script>
    $(function() {
        $('.btn-add-family').click(function() {
            let container = $('.inputPenutupan'),
                newRow = $('.container-form-penutupan'),
                counter = parseInt(container.find('.counter').length);
            
            newRow.append(`
            <div class="counter">
                <h4 class="title mt-4">
                    Data Anak/Lainnya
                </h4>
                <div class="mt-3">
                    <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="penutupan[`+counter+`][insured_title]"
                        title="TITLE" required>
                        <option value="nyonya">Nyonya</option>
                        <option value="tuan">Tuan</option>
                        <option value="nona">Nona</option>
                    </select>
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[`+counter+`][insured_firstname]"
                        value="" placeholder="NAMA DEPAN">
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[`+counter+`][insured_lastname]"
                        value="" placeholder="NAMA BELAKANG">
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[`+counter+`][insured_birthplace]"
                        value="" placeholder="TEMPAT LAHIR">
                </div>
                <div class="mt-3">
                    <div class="input-group has-primary">
                        <input type="text" class="form-control datepicker bg-section type-1 pl-4" id="insuredbirth-`+counter+`"
                            value="" placeholder="TANGGAL LAHIR" name="penutupan[`+counter+`][insured_dob]">
                    </div>
                </div>
                <div class="mt-3">
                    <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="penutupan[`+counter+`][insured_identity]"
                        title="PILIHAN IDENTITAS" required>
                        <option value="KTP">KTP</option>
                        <option value="Passport">PASPOR</option>
                    </select>
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[`+counter+`][insured_noindentity]"
                        value="" placeholder="NO IDENTITAS">
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control type-1 pl-4 text-dark bg-section" name="penutupan[`+counter+`][insured_alamat]"
                        value="" placeholder="ALAMAT TEMPAT TINGGAL">
                </div>
                <div class="mt-3">
                    <select class="selectpicker w-100 text-dark desArea pb-5 bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="penutupan[`+counter+`][insured_relationship]"
                        title="HUBUNGAN DENGAN PEMEGANG POLIS" required>
                        <option value="Spouse">Pasangan</option>
                        <option value="Child">Anak</option>
                        <option value="Family">Keluarga</option>
                        <option value="Friend">Teman</option>
                        <option value="Other">Lainnya</option>
                    </select>
                </div>
            </div>
            `);
            $('#insuredbirth-'+counter).datetimepicker({
                format: 'DD MMM YYYY',
                maxDate: new Date(),
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-remove'
                }
            });
            $('select').selectpicker();
            $('#insuredbirth-'+counter).val("");
        })
        $(".btn-complete").click(function() {
            if(!$('input[name=tnc]').is(':checked')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!..',
                    text: 'Terms and Condition wajib diisi!',
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
            let penutupan = $(".formDataPenutupan").serializeArray();
            var obj = {};

            for (key in penutupan) {
                obj[penutupan[key].name] = penutupan[key].value;
            };

            console.log(obj);

            $.ajax({
                type: "POST",
                data: obj,
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('travel.submitpenutupan', $order->id) }}",
                success: function(res) {
                    if(res.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Order telah berhasil, silahkan lakukan pembayaran.'
                        });
                        callback.postMessage('/');
                    }
                },
                error: function (err, textStatus, errorThrown) {
                    let res = JSON.parse(err.responseText);
                    if(err.status == 422) {
                        let htmls;
                        let validator = res.data;
                        htmls = "<ul class='text-left'>"
                        for(var i in validator) {
                            htmls += "<li class='mb-3'>"+ validator[i][0] +"</li>"
                        }
                        htmls += "</ul>"
                        Swal.fire({
                            icon: 'error',
                            title: 'Terdapat kesalahan',
                            html: htmls,
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
                    }else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!..',
                            text: res.message,
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
            })
        })
    });
</script>
<script>
    $(function() {
        $('.tnc-downloader').click(function() {
            var link = "{{ asset('assets/travel/Riplay_Zurich_Travel_Insurance_-_Umum.pdf') }}";
            callback.postMessage(link);
        });
    })
</script>