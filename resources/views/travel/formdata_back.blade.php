<div class="policyHolder mt-5" style="display:none;">
    <div class="section">
        <div class="features ">
            <form id="TravellersInfo" onsubmit="submitTraveller(this)">
                @for($ie = 0; $ie < 10; $ie++)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-nav-tabs">
                                <div class="card-header card-header-info">
                                    <div class="nav-tabs-navigation">
                                        <div class="nav-tabs-wrapper">
                                            <ul class="nav nav-tabs" data-tabs="tabs">
                                                <li class="nav-item">
                                                    @php
                                                        if($ie == 0) {
                                                            $title = "Insured (Policy Holder)";
                                                        }elseif($ie == 1) {
                                                            $title = "Traveler <span id='idtitle'></span>";
                                                        }elseif($ie > 1) {
                                                            $title = "Traveler (Child)";
                                                        }
                                                    @endphp
                                                    <a class="nav-link disabled" href="#single"
                                                        data-toggle="tab">
                                                        <span class="badge badge-pills badge-success mr-3">{{ $ie + 1 }}</span> {!! $title !!}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-5">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="title_insurer">Title</label>
                                                <select class="form-control selectpicker" name="title[{{$ie}}]" data-style="btn btn-link" id="title_insurer">
                                                    <option value="mr" {{$ie == 0 ? "selected" : ""}}>MR</option>
                                                    <option value="ms">MS</option>
                                                    <option value="mrs" {{$ie == 1 ? "selected" : ""}}>MRS</option>
                                                    <option value="mstr" {{$ie == 2 ? "selected" : ""}}>MSTR</option>
                                                    <option value="miss">MISS</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="firstName">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control mt-3" name="first_name[{{$ie}}]" id="firstName" placeholder="First Name">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="lastName">Last Name</label>
                                                        <input type="text" class="form-control mt-3" id="lastName" name="last_name[{{$ie}}]" placeholder="Last Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="plaecofBirth">Place of Birth</label>
                                                        <input type="text" class="form-control mt-3" id="plaecofBirth" name="place_birth[{{$ie}}]" placeholder="Place of Birth">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="dateofbirth">Date of Birth <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <select class="form-control selectpicker" name="date_birth[{{$ie}}]" data-style="btn btn-link" id="dateofbirth">
                                                                    <option selected disabled></option>
                                                                    @for($i = 1; $i < 32; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <select class="form-control selectpicker" name="month_birth[{{$ie}}]" data-style="btn btn-link">
                                                                    <option selected disabled></option>
                                                                    @for($i = 1; $i < 13; $i++)
                                                                        <option value="{{ $i }}">{{ date('M', mktime(0,0,0,$i, 1, date('Y'))) }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <select class="form-control selectpicker" name="year_birth[{{$ie}}]" data-style="btn btn-link">
                                                                    <option selected disabled></option>
                                                                    @for($i = date('Y'); $i > (date('Y') - 71); $i--)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <label for="idNumber">ID Number <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="col-md-6 col-5">
                                                            <div class="form-group">
                                                                <select class="form-control selectpicker mt-3" name="identity[{{$ie}}]" data-style="btn btn-link" id="idNumber">
                                                                    <option value="KTP">KTP/NIK</option>
                                                                    <option value="KITAS">KITAS</option>
                                                                    <option value="Passport">PASSPORT</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-7">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="id_number[{{$ie}}]" id="id_number" placeholder="ID Number">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <span class="small text-muted">
                                                                *If the passenger does not have a NIK, please fill the ID number with 0000
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($ie == 0)
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control mt-4" name="phone_number" id="phone" placeholder="Mobile Phone">
                                                        </div>
                                                    </div>
                                                @elseif($ie == 1)
                                                    <div class="col-md-6">
                                                        <label class="">Relationship with policy holder <span class="text-danger">*</span></label>
                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label text-muted">
                                                                <input class="form-check-input relationships" type="radio" name="relationships" id="relationships" value="Spouse">
                                                                Spouse
                                                                <span class="circle">
                                                                    <span class="check"></span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label">
                                                                <input class="form-check-input relationships" type="radio" name="relationships" id="relationships" value="Child">
                                                                Child
                                                                <span class="circle">
                                                                    <span class="check"></span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($ie == 0)
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="email">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control mt-4" name="email" id="email" placeholder="name@example.com">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="additional_email">Additional Email</label>
                                                            <input type="email" class="form-control mt-4" name="additional_email" id="additional_email" placeholder="name@example.com">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="city">City <span class="text-danger">*</span></label>
                                                        <select class="selectpicker w-100 cities mt-3" data-live-search="true"
                                                            data-style="select-with-transition" data-container="false" name="cities" title="Select an option"
                                                            data-size="7" required>
                                                            @foreach ($cities as $city)
                                                                <option value="{{ $city['Name'] }}">{{ $city['Name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="postal_code">Postal Code</label>
                                                        <input type="text" class="form-control mt-4" id="postal_code" placeholder="Postal Code">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="address">Address <span class="text-danger">*</span> </label>
                                                        <textarea class="form-control" id="address" name="address[{{$ie}}]" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
                <button type="button" class="btn btn-success btn-block" id="submitTraveller">Submit</button>
            </form>
        </div>
    </div>
</div>