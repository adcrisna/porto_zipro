<script>
    $(document).ready(function() {
        var region_val = $('.desArea').find(":selected").val(); 
        var region_text = $('.desArea').find(":selected").text().toUpperCase();
        if(region_val == 1 && region_text.includes("DOMES")) {
            console.log(region_text);
            $("input[name=alreadyTrav][value='no']").prop("checked",true);
            $('.formDestination').show();
            $("input[name=alreadyTrav]").attr("disabled", true);
        }else {
           $("input[name=alreadyTrav][value='no']").prop("checked",false);
           $("input[name=alreadyTrav]").removeAttr("disabled");
           $('.formDestination').find('input select').val("");
           $('.formDestination').hide();
        }
        var endDate = new Date($('#birthDate').data('DateTimePicker').date());
        let today = new Date().getFullYear();
        let birth = endDate.getFullYear();
        let diff = parseInt(today) - parseInt(birth);
        if(diff > 70) {
            // $('.divTypeProduct').empty();
            // $('.divTypePlan').empty();

            $('.typeProduct option').each(function() {
                if ( $(this).val() !== 'Individual' ) {
                    $(this).remove();
                }
            });
            $('.typePlan option').each(function() {
                var value_options = String($(this).val());
                if ( value_options !== "21" && value_options !== "22" ) {
                    $(this).remove();
                }
            });
            $('select').selectpicker();
        }
    })
</script>


<script>
    $("input[name=age]").on('change', function () {
        $('#formAlreadyTrav').show();
    });

    $('#startDate').datetimepicker({
        format: 'DD MMM YYYY',
        // date: new Date(),
        minDate: new Date().setDate(new Date().getDate() - 1),
        disabledDates: [
            new Date().setDate(new Date().getDate() - 1)
        ],
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
    $('#birthDate').datetimepicker({
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
    $('#endDate').datetimepicker({
        format: 'DD MMM YYYY',
        // date: new Date(),
        minDate: new Date(),
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

    // INIT DATE 
    // INIT VAL DATA DATE
    $('#startDate').val("{{ $data['start_date'] }}");
    $('#birthDate').val("{{ $data['birth'] }}");
    $('#endDate').val("{{ $data['end_date'] }}");
    $('#inputdepartureDate').val("{{ $data['depature_date'] }}");

    const datenow = new Date();
    let minDate = datenow.setDate(datenow.getDate() - 3);
    let initDate = $('#startDate').data('DateTimePicker').date()
        $('#inputdepartureDate').datetimepicker({
            format: 'DD MMM YYYY',
            // date: new Date(),
            maxDate: new Date(),
            minDate: minDate,
            disabledDates: [
                new Date().setDate(new Date().getDate() - 3)
            ],
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
    // }
    $("input[name=alreadyTrav]").on('change', function (e) {
        $("#disclaimer").prop("checked", false);
        if ($(this).val() == 'yes') {
            $(".alreadytravel-date").show();
            $(".formDestination").hide();
            $("#startDate").prop("disabled", true);
            $("#startDate").data('DateTimePicker').date(new Date());
        } else {
            $(".alreadytravel-date").hide();
            $(".formDestination").show();
            $("#startDate").prop("disabled", false);
        }
    });

    $('#inputdepartureDate').on('dp.change', function (e) {
        $('.card-disclaim').show();
        var depatureDate = new Date(e.date);
        console.log(depatureDate);
        $('#startDate').data('DateTimePicker').date(depatureDate)
    });

    $('#startDate').on('dp.change', function (e) {
        e.preventDefault()
        var type_plan = $('.typePlan').find(':selected').val()
        console.log(type_plan);
        $('#endDate').datetimepicker({
            format: 'DD MMM YYYY',
            // maxDate: new Date(new Date(e.date).setDate(new Date(e.date).getDate() + 2)),
            minDate: new Date(e.date),
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
        })
        if(type_plan == "2" || type_plan == "3") {
            $('#endDate').data('DateTimePicker').clear();
            let endDate = $('#endDate').data('DateTimePicker');
            var startDate = new Date(e.date);
            var dateEnd = new Date(endDate.date());
            var yearly = new Date(new Date(e.date).setDate(new Date(e.date).getDate() + 365))
            endDate.date(yearly);
            let days = getDatesInRange(startDate, yearly);
            if(days == 0) {
                days = 1;
            }
            $("#days").val(days)   

        }else if(type_plan == "22") {
            $('#endDate').data('DateTimePicker').maxDate();
            $('#endDate').removeAttr('disabled');
            $('#endDate').prop('disabled', false);
            $('#endDate').val("");
            let endDate = $('#endDate').data('DateTimePicker');
            var startDate = new Date(e.date);
            var dateEnd = new Date(endDate.date());
            if (startDate.getDate() > dateEnd.getDate()) {
                endDate.date(startDate)
            }
            var maxdat = new Date(new Date(e.date).setDate(new Date(e.date).getDate() + 2));

            endDate.maxDate(false);
            endDate.minDate(e.date);
            endDate.maxDate(maxdat);
            let days = getDatesInRange(startDate, dateEnd);
            if(days == 0) {
                days = 1;
            }
            $("#days").val(days)   

        }else {
            $('#endDate').data('DateTimePicker').clear();
            $('#endDate').removeAttr('disabled');
            $('#endDate').prop('disabled', false);
            $('#endDate').val("");
            let endDate = $('#endDate').data('DateTimePicker');
            var startDate = new Date(e.date);
            var dateEnd = new Date(endDate.date());
            if (startDate.getDate() > dateEnd.getDate()) {
                endDate.date(startDate)
            }
            endDate.minDate(e.date);
            endDate.maxDate();
            let days = getDatesInRange(startDate, dateEnd);
            if(days == 0) {
                days = 1;
            }
            $("#days").val(days)   
        }
    });

    $('#endDate').on('dp.change', function (e) {
        // e.preventDefault()
        const start = $('#startDate').data('DateTimePicker').date();
        let endDate = $('#endDate').data('DateTimePicker')
        var date = new Date(e.date);
        let startDate = new Date(start)
        endDate.date(date)
        let days = getDatesInRange(startDate, date);
        if(days == 0) {
            days = 1;
        }
        $("#days").val(days)
    });

    function getDatesInRange(startDate, endDate) {
        const date = new Date(startDate.getTime());

        // const dates = [];
        var counter = 0;

        while (date <= endDate) {
            // dates.push(new Date(date));
            counter = counter + 1;
            date.setDate(date.getDate() + 1);
        }

        // return dates;
        return counter;
    }
</script>
<script>
    $(".days").on('input', function (e, state) {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        if(e.originalEvent === undefined){
            return
        };

        let value = $(this).val() - 1;
        console.log(value)
        if(value == 0) {
            value = 1;
        }
        const start_date = new Date();
        let changeDate = moment(start_date, "DD MMM YYYY").add('days', value);
        if($('#startDate').val() == "") {
            $('#startDate').data('DateTimePicker').date(start_date)
        };
        $('#endDate').data('DateTimePicker').date(changeDate)
    })
</script>

<script>
    $('.desArea').on('change', function (e) {
        var birth = $('#birthDate').data('DateTimePicker').date();
        if(birth == null) {
            customAlert('error', "Silahkan isi tanggal lahir terlebih dahulu")
            $(this).find(":selected").prop("selected", false)
            return;
        }
        var ends = $('#endDate').data('DateTimePicker');
        console.log(ends);
        if(typeof ends !== "undefined") {
            $("#days").val(0)
            $('#endDate').data('DateTimePicker').destroy();
        }
        $('#startDate').val("");
        $('#endDate').val("");

        $('.desCountry').remove();
        let val = $(this).find(":selected").val()
        let text = $(this).find(":selected").text().toUpperCase();

        if(val == 1 && text.includes("DOMES")) {
            console.log(text);
            $("input[name=alreadyTrav][value='no']").prop("checked",true);
            $('.formDestination').show();
            $("input[name=alreadyTrav]").attr("disabled", true);
        }else {
           $("input[name=alreadyTrav][value='no']").prop("checked",false);
           $("input[name=alreadyTrav]").removeAttr("disabled");
           $('.formDestination').find('input select').val("");
           $('.formDestination').hide();
        }
        $('.divcountry').append(`
            <div class="row container-loader">
                <div class="col-md-12 text-center">
                    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div></div>
                </div>
            </div>
        `)
        $('.divcountry').append(
            `<select class="selectpicker w-100 desCountry bg-section type-1 pb-5" data-live-search="true"
                                    data-style="select-with-transition" name="destinationCountry"
                                    title="NEGARA TUJUAN" data-size="7" data-size="7" required></select>`
        )
        $.ajax({
            type: "GET",
            data: {
                area_id: val
            },
            headers: {
                'Authorization': 'Bearer '+"{{ $token }}"
            },
            url: "{{ route('travel.getcountry') }}" + "?area_id=" + val,
            success: function (res) {
                res['country'].forEach((v, k) => {
                    $('.divcountry').find('.container-loader').remove();
                    $('select.desCountry').append(`<option value="` + v.ID + `">` + v
                        .Name + `</option>`)
                    $('select.desCountry').selectpicker();
                });
            },
            error: function(err, textStatus, errorThrown) {
                let res = JSON.parse(err.responseText);
                if(err.statusCode == 401) {
                    res.message = "Sesi telah berakhir, silahkan login untuk melanjutkan"
                }
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
                });
                callback.postMessage('/');
            }
        });
    });
</script>
<script>
    $(function() {
        $(".relationships").on('change', function() {
            if( $(this).is(':checked') ) {
                $("#idtitle").text("("+$(this).val()+")");
                console.log($(this).val());
            }
        });
    })
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // $(".btn-submit-section").click(function() {
    //     return initSubmit($('.formAction'));
    // })

    function initSubmit(form) {
        $(form).find('input,select').attr('required', true)
        $('.additional_coverage').hide();
        $('.form__summary').hide();
        let data = $(form).serializeArray()
        let start, end, birth;
        if($("#startDate").val() !== "") {
            start = new Date($("#startDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
        }
        if($("#endDate").val() !== "") {
            end = new Date($("#endDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
        }
        if($("#birthDate").val() !== "") {
            birth = new Date($("#birthDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
        }
        let start_date = {
            name: "start_date",
            value: start
        }
        let end_date = {
            name: "end_date",
            value: end
        }

        let birth_date = {
            name: "birth",
            value: birth
        }

        data.push(start_date);
        data.push(end_date);
        data.push(birth_date);
        let newdata = {};
        for (i in data) {
            newdata[data[i].name] = data[i].value
        }
        $('.productResult').empty();
        $('.productResult').addClass('text-center');
        $('.productResult').append(`<div class="row container-loader">
                    <div class="col-md-12 text-center">
                        <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div></div>
                    </div>
                </div>`);
        $.ajax({
            type: "POST",
            data: {
                data: newdata
            },
            headers: {
                'Authorization': 'Bearer '+"{{ $token }}"
            },
            url: "{{ route('travel.getProduct') }}",
            success: function (res) {
                $('.productResult').removeClass('text-center');
                $('.productResult').empty();
                $('.productResult').append(res);
                $('.nav-typeOfPlan').text($('.typePlan option:selected').text())
                $('.nav-select-product').click(function() {
                    $('.table_sum').empty();
                    $('.nav-select-product').removeClass('bg-primary-3').addClass('bg-primary-4');
                    $('.additional_coverage').show();
                    $('.form__summary').show();
                    let key = $(this).attr('data-keys');
                    let product_id = $(this).attr('data-ids');
                    let plan_id = $(this).attr('data-planIds');
                    let data_product = JSON.parse($(this).attr('data-product'));

                    console.log(data_product);
                    $('#sum-product-cat').text(data_product.TravellerTypeName);
                    $('#sum-product-plan').text(data_product.PlanName);
                    $('#sum-product-pax').text('Rp '+ data_product.MainRate.toLocaleString('id-ID'));
                    $('#sum-product-pax').attr('price', data_product.MainRate);
                    $('#total-price').text('Rp '+data_product.MainRate.toLocaleString('id-ID'))
                    // lefthand text
                    $('.button-select').text('PILIH');
                    $('.button-select-'+ key).html('Terpilih');
                    $('#card-loop-'+key).addClass('active');
                    $('.btn-continue').show();


                    // Adding additional Value
                    $("#zurich_product_id").val(product_id);
                    $("#zurich_plan_id").val(plan_id);
                    $("#zurich_product_name").val(data_product.PlanName);
                    $('.policyHolder').show();
                    $(".card-product").each(function() {
                        if(!$(this).hasClass("active")) {
                            $(this).remove();
                        }
                    });
                    $('.additional_coverage').empty();
                    $.ajax({
                        type: 'POST',
                        data: {
                            data: newdata
                        },
                        headers: {
                            'Authorization': 'Bearer '+"{{ $token }}"
                        },
                        url: '{{ url("/api/travel/get-coverage/") }}' + `/${product_id}`,
                        success: function(res) {
                            $('.additional_coverage').append(res);
                            $('.optional').click(function() {
                                var product_price = $('#sum-product-pax').attr('price');
                                var price = parseInt(product_price);
                                var coverage = [];
                                $('.table_sum').empty();
                                $('.row-add-product').empty();
                                $('.optional:checked').each(function(i) {
                                    let data = JSON.parse($(this).attr('data-opsi'))
                                        price = parseInt(price) + parseInt(data.MainRate);
                                        $('.row-add-product').append(`
                                            <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-add-loop">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <div class="text-center">
                                                            <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                                                <b style="color:#263570;" class="add_name_card-">`+ data.Name +`</b>
                                                            </span>
                                                        </div>
                                                        <div class="text-left pt-3">
                                                            <div class="row">
                                                                <div class="col-md-12 mx-auto text-center">
                                                                    <img src="{{ asset('assets/travel/img/product/checkup.png') }}" alt="ig" class="img-fluid">
                                                                </div>
                                                                <div class="col-md-12 des_add_cover">
                                                                    <p>`+data.Description+`</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="container text-center">
                                                            <h4 class="h-1 text-primary">
                                                                <b style="color:#263570" class="add_pax_price">Rp `+ data.MainRate.toLocaleString('id-ID') +`</b>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `)
                                        $('.table_sum').append(`
                                            <div class="col-md-12 mt-2">
                                                <span class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                                                    Perlindungan Tambahan
                                                </span>
                                                <span style="color:#263570; font-weight: bold; font-size: 16px">
                                                    `+ data.Name +`
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
                                                                <span data-price="`+data.MainRate+`" style="font-size: 13px;"> Rp `+ data.MainRate.toLocaleString('id-ID') +` </span>
                                                            </td>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        `)
                                    coverage[i] = data;
                                });
                                $('#total-price').text('Rp'+price.toLocaleString('id-ID')+',-');
                            })
                        },
                        error: function(err, textStatus, errorThrown) {
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
                    });

                    $(function() {
                        $(".relationships").on('change', function() {
                            if( $(this).is(':checked') ) {
                                $("#idtitle").text("("+$(this).val()+")");
                                console.log($(this).val());
                            }
                        });
                        $('.card-product').find('.active:not(:eq(0))').remove()
                    });

                });
                $('.des_product').click(function() {
                    callback.postMessage('https://cdn.salvusnetwork.com/zipro2/new-coverage.pdf');
                })
            },
            error: function (err, textStatus, errorThrown) {
                $('.productResult').empty();
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
        });
    }
</script>

<script>
    $(function() {
        $(".nav-select-product").click(function() {
            $('.card-product').find('.active:not(:eq(0))').remove()
        })
    })
</script>
<script>
    $(function() {
        $('.optional').click(function() {
            var product_price = $('#sum-product-pax').attr('price');
            var price = parseInt(product_price);
            console.log(product_price)
            var coverage = [];
            $('.table_sum').empty();
            $('.optional:checked').each(function(i) {
                let data = JSON.parse($(this).attr('data-opsi'))
                    price = parseInt(price) + parseInt(data.MainRate);
                    $('.table_sum').append(`
                        <div class="col-md-12 mt-2">
                            <span class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                                Perlindungan Tambahan
                            </span>
                            <span style="color:#263570; font-weight: bold; font-size: 16px">
                                `+ data.Name +`
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
                                            <span data-price="`+data.MainRate+`" style="font-size: 13px;"> Rp `+ data.MainRate.toLocaleString('id-ID') +` </span>
                                        </td>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    `)
                coverage[i] = data;
            });
            console.log(parseInt(price));
            $('#total-price').text('Rp'+price.toLocaleString('id-ID')+',-');
        })

        $('.btn-continue').click(function() {
            let data = $("#TravellersInfo").serializeArray();
            let action = $(".formAction").serializeArray();
            let penawaran = $(".FormPenawaran").serializeArray();
            let coverage = [];
            $('.optional:checked').each(function(keyCover) {
                let data = JSON.parse($(this).attr('data-opsi'))
                coverage[keyCover] = data.ID;
            });
            console.log("total coverage");
            console.log(coverage);
            
            let json_coverage = {
                name: "coverages",
                value: coverage
            } 
            let start_date = {
                name: "start_date",
                value: new Date($("#startDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }
            let end_date = {
                name: "end_date",
                value: new Date($("#endDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }

            let depatureDate = {
                name: "depature_date",
                value: new Date($("#inputdepartureDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }

            let ZurichOriginsName = {
                name: "zurich_origin_name",
                value: $('.desCountry :selected').text()
            }


            action.push(start_date);
            action.push(end_date);
            action.push(depatureDate);
            action.push(json_coverage);
            data.push(ZurichOriginsName);

            var obj = {};

            for (i in action) {
                obj[action[i].name] = action[i].value
            }
            
            for (index in data) {
                obj[data[index].name] = data[index].value;
            };

            for (key in penawaran) {
                obj[penawaran[key].name] = penawaran[key].value;
            };

            console.log(obj);

            $.ajax({
                type: "POST",
                data: obj,
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('travel.summarypage') }}",
                success: function(res) {
                    if(res.status == true) {
                        $('.form__summary').show();
                        $('.form__input').hide();
                        $('#sum-product-cat').text();
                        var product_price = $('#sum-product-pax').attr('price');
                        var price = parseInt(product_price);
                        var coverage = [];
                        $('.table_sum').empty();
                        $('.optional:checked').each(function(i) {
                            let data = JSON.parse($(this).attr('data-opsi'))
                            price = parseInt(price) + parseInt(data.MainRate);
                            $('.table_sum').append(`
                                <div class="col-md-12 mt-2">
                                    <span class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                                        Perlindungan Tambahan
                                    </span>
                                    <span style="color:#263570; font-weight: bold; font-size: 16px">
                                        `+ data.Name +`
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
                                                    <span data-price="`+data.MainRate+`" style="font-size: 13px;"> Rp `+ data.MainRate.toLocaleString('id-ID') +` </span>
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            `)
                            coverage[i] = data;
                        });
                        // Rp360.000,-
                        $('#total-price').text('Rp'+price.toLocaleString('id-ID')+',-');
                        console.log(coverage);
                    }
                },
                error: function (err, textStatus, errorThrown) {
                    let res = JSON.parse(err.responseText);
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
            })
        });
    });
</script>
<script>
    $(function() {
        $(".btn-penutupan").click(function() {
            let data = $("#TravellersInfo").serializeArray();
            let action = $(".formAction").serializeArray();
            let coverage = [];
            $('.optional:checked').each(function(keyCover) {
                let data = JSON.parse($(this).attr('data-opsi'))
                coverage[keyCover] = data.ID;
            });
            console.log("total coverage");
            console.log(coverage);
            
            let json_coverage = {
                name: "coverages",
                value: coverage
            } 
            let start_date = {
                name: "start_date",
                value: new Date($("#startDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }
            let end_date = {
                name: "end_date",
                value: new Date($("#endDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
            }
            if($.trim($("#inputdepartureDate").html()) !== '') {
                let depatureDate = {
                    name: "depature_date",
                    value: new Date($("#inputdepartureDate").data("DateTimePicker").date()).toLocaleDateString("id-ID")
                }
                action.push(depatureDate);
            }

            let ZurichOriginsName = {
                name: "zurich_origin_name",
                value: $('.desCountry :selected').text()
            }

            action.push(start_date);
            action.push(end_date);
            action.push(json_coverage);
            data.push(ZurichOriginsName);

            var obj = {};

            for (i in action) {
                obj[action[i].name] = action[i].value
            }
            
            for (index in data) {
                if(data[index].name == "destinationCountry") {
                    obj["destinationCountry"] = $('.desCountry :selected').val();
                }else {
                    obj[data[index].name] = data[index].value;
                }
            };

            console.log(obj);

            $.ajax({
                type: "POST",
                data: obj,
                headers: {
                    'Authorization': 'Bearer '+"{{ $token }}"
                },
                url: "{{ route('travel.updateoffering', $order->id) }}",
                success: function(res) {
                    if(res.status == true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Penarawan berhasil dirubah.'
                        });
                        callback.postMessage('/');
                    }
                },
                error: function (err, textStatus, errorThrown) {
                    let res = JSON.parse(err.responseText);
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
            })
        })
    })
</script>
<script>
$('.des_product').click(function() {
    callback.postMessage('https://cdn.salvusnetwork.com/zipro2/new-coverage.pdf');
})
</script>

<script>
    $(function() {
        $('#birthDate').on('dp.change', function(e) {
            console.log('mazuk')
            let today = new Date().getFullYear();
            let birth = new Date(e.date).getFullYear();
            let diff = parseInt(today) - parseInt(birth);
            if(diff > 70) {
                $('.divTypeProduct').empty();
                $('.divTypePlan').empty();

                $('.divTypeProduct').append(`
                    <select class="selectpicker w-100 pb-5 text-dark typeProduct bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="product_type" title="TIPE PRODUK"
                        data-size="7" required>
                            <option value="Individual">INDIVIDU</option>
                    </select>
                `);
                $('.divTypePlan').append(`
                    <select class="selectpicker w-100 pb-5 text-dark typePlan bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="package_type" title="TIPE PAKET"
                        data-size="7" required>
                            <option value="21">Single Trip</option>
                            <option value="22">One Way Trip</option>
                            
                    </select>
                `);
                $('select').selectpicker();
            }else {
                $('.divTypeProduct').empty();
                $('.divTypePlan').empty();
                $('.divTypeProduct').append(`
                    <select class="selectpicker w-100 pb-5 text-dark typeProduct bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="product_type" title="TIPE PRODUK"
                        data-size="7" required>
                            <option value="Individual">INDIVIDU</option>
                            <option value="Family">KELUARGA</option>
                            <option value="Duo Plus">DUO PLUS</option>
                    </select>
                `);
                $('.divTypePlan').append(`
                    <select class="selectpicker w-100 pb-5 text-dark typePlan bg-section type-1" data-live-search="true"
                        data-style="select-with-transition" name="package_type" title="TIPE PAKET"
                        data-size="7" required>
                            <option value="2">Yearly 90</option>
                            <option value="3">Yearly 180</option>
                            <option value="21">Single Trip</option>
                            <option value="22">One Way Trip</option>
                    </select>
                `);
                $('select').selectpicker();
            }
            var ends = $('#endDate').data('DateTimePicker');
            console.log(ends);
            if(typeof ends !== "undefined") {
                $("#days").val(0)
                $('#endDate').data('DateTimePicker').destroy();
            }
            $('#startDate').val("");
            $('#endDate').val("");
        });
    })
</script>

<script>
    $('.optional').click(function() {
        var product_price = $('#sum-product-pax').attr('price');
        var price = parseInt(product_price);
        var coverage = [];
        $('.table_sum').empty();
        $('.row-add-product').empty();
        $('.optional:checked').each(function(i) {
            let data = JSON.parse($(this).attr('data-opsi'))
                price = parseInt(price) + parseInt(data.MainRate);
                $('.row-add-product').append(`
                    <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-add-loop">
                        <div class="card">
                            <div class="card-body ">
                                <div class="text-center">
                                    <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                        <b style="color:#263570;" class="add_name_card-">`+ data.Name +`</b>
                                    </span>
                                </div>
                                <div class="text-left pt-3">
                                    <div class="row">
                                        <div class="col-md-12 mx-auto text-center">
                                            <img src="{{ asset('assets/travel/img/product/checkup.png') }}" alt="ig" class="img-fluid">
                                        </div>
                                        <div class="col-md-12 des_add_cover">
                                            <p>`+data.Description+`</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="container text-center">
                                    <h4 class="h-1 text-primary">
                                        <b style="color:#263570" class="add_pax_price">Rp `+ data.MainRate.toLocaleString('id-ID') +`</b>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                `)
                $('.table_sum').append(`
                    <div class="col-md-12 mt-2">
                        <span class="mr-3" style="color:#1363B4; font-weight: bold; font-size: 16px">
                            Perlindungan Tambahan
                        </span>
                        <span style="color:#263570; font-weight: bold; font-size: 16px">
                            `+ data.Name +`
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
                                        <span data-price="`+data.MainRate+`" style="font-size: 13px;"> Rp `+ data.MainRate.toLocaleString('id-ID') +` </span>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                `)
            coverage[i] = data;
        });
        $('#total-price').text('Rp'+price.toLocaleString('id-ID')+',-');
    })
</script>

<script>
    $(function() {
        $(document).on('click', '.typePlan', function(e) {
            var val = $(this).find(':selected').val();
            $('#startDate').data('DateTimePicker').destroy();
            $('#startDate').val("");
            $('#endDate').val("");
            $('#endDate').prop('disabled', true)
            if(val !== undefined || val !== null) {
                $('#startDate').removeAttr('disabled');
                $('#startDate').prop('disabled', false);
                $('#startDate').datetimepicker({
                    format: 'DD MMM YYYY',
                    minDate: new Date().setDate(new Date().getDate() - 1),
                    disabledDates: [
                        new Date().setDate(new Date().getDate() - 1)
                    ],
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
            }else {
                customAlert('error', "Silahkan pilih Tipe Paket")
            }
        })
    });

    function customAlert(type, msg = "Internal Server Error", code = 500)
    {
        switch (type) {
            case 'error':
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!..',
                    text: msg,
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
                break;
        
            default:
                break;
        }

    }
</script>