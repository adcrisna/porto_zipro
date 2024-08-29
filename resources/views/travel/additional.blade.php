 <div class="sub-judul bg-section mb-1 shadow-none">
    <div class="font-weight-normal pt-2 pl-2 text-left">
        <p class="p-text">
        PERLINDUNGAN TAMBAHAN (OPTIONAL)
        </p>
    </div>
</div>
@foreach ($products as $coverage)
    <div class="container sub-judul mb-1 shadow-none mt-3">
        <div class="d-flex justify-content-between py-2">
            <p>{{ $coverage['Name'] }}</p>
            <div class="form-check">
            <label class="form-check-label">
                <input class="form-check-input input-{{ $coverage['ID'] }} optional" type="checkbox" value="{{ $coverage['ID'] }}" name="additional_coverage[]" 
                    data-opsi="{{ json_encode($coverage,true) }}">
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

    </div>
</div>