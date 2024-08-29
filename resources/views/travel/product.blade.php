 <div class="sub-judul mb-1 bg-section shadow-none">
     <div class="font-weight-normal pt-2 pl-2 text-left">
         <p class="p-text">
             PILIHAN PAKET
         </p>
     </div>
 </div>
 <div class="container">
     <input type="hidden" id="zurich_product_id" name="zurich_product_id">
     <input type="hidden" id="zurich_plan_id" name="zurich_plan_id">
     <input type="hidden" id="zurich_product_name" name="zurich_product_name">
     <div class="row mb-3">

        @foreach ($products as $keys => $product)
            <div class="col-md-12 ml-auto mr-auto m-2 card-product" id="card-loop-{{ $keys }}">
                <div class="card">
                    <div class="card-body ">
                        <div class="text-center">
                            <span class="media-heading font-weight-bold" style="font-size: 1rem;">
                                <b style="color:#263570;" class="">{{ $product['TravellerTypeName'].' - '.$product['PlanName'] }}</b>
                            </span>
                        </div>
                        <div class="text-left pt-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <p><b>Manfaat</b>: </p>
                                </div>
                                <div class="col-md-12">
                                    <ul class="nav flex-column nav-cover-product-{{$keys}}">
                                        @php
                                            $last = [];
                                        @endphp
                                        @foreach ($product['coverages'] as $key => $coverage)
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
                                <b style="color:#263570">Rp {{ number_format($product['MainRate'], 0, ',', '.') }}</b>
                            </h4>
                        </div>
                        <div class="d-flex justify-content-between w-100 mt-3">
                            <a href="javascript:;" class="btn btn-white btn-round btn-block ml-2 btn-coverage des_product"
                                style="border: 2px solid #263570;" data-key="{{ $keys }}" data-last="{{ json_encode($last,true) }}">
                                <span class="media-heading font-weight-bold">
                                    <b style="color:#263570" >Deskripsi</b>
                                </span>
                            </a>

                            <a href="javascript:;"
                                class="btn bg-primary-4 btn-round nav-select-product button-clr-{{$keys}} ml-2 btn-block"
                                data-ids="{{ $product['ID'] }}" data-product="{{json_encode($product,true)}}" data-keys="{{$keys}}" data-planIds="{{ $product['PlanID'] }}">
                                <span class="media-heading font-weight-bold button-select-{{$keys}} button-select">
                                    <b>PILIH</b>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

     </div>
 </div>



