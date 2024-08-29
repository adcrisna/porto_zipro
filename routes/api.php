<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CalculateController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\AdiraController;
use App\Http\Controllers\MVController;
use App\Http\Controllers\history;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MikroController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefController;
use App\Http\Controllers\PerjalananController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\BackorderController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\MotoController;
use App\Http\Controllers\RetryController;
use App\Http\Controllers\TriggerController;
use App\Http\Middleware\Tracking;

// Authentication
Route::middleware('guest')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/forgot', [RegisterController::class, 'forgot']);

    Route::controller(BankController::class)->prefix('bank')->group(function () {
        Route::get('/', 'list');
    });
    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('/', 'list');
        Route::get('/detail/{id}', 'detailProduct');
    });

    Route::controller(CategoryController::class)->prefix('category')->group(function () {
        Route::get('/', 'list');
    });
    Route::controller(BannerController::class)->prefix('banner')->group(function () {
        Route::get('/', 'list');
        Route::get('/{slug}', 'detail')->name('banner.page');
    });

    Route::post('/syncTransaction', [TestController::class, 'syncTransaction']);
    Route::post('/syncTransactionApproved', [TestController::class, 'syncTransactionApproved']);
    Route::post('/migrations', [TestController::class, 'migrations']);
    Route::get('/test', [TestController::class, 'index']);
    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('/search', 'search');
    });
});
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/getResponse', [TestController::class, 'getResponse']);
Route::post('/trigger/create-policy', [TriggerController::class, 'createPolicy']);
//Rehit
Route::controller(RetryController::class)->group(function () {
    Route::get('/retryadira', 'retryadira');
    Route::get('/retryfinish', 'retryfinish');
    Route::get('/retryMikro', 'retryMikro');
    Route::get('/retryPerjalanan', 'retryPerjalanan');
    Route::get('/retryTravel', 'retryTravel');
    Route::get('/retryResponse/{id}', 'retryResponse');
    Route::get('/retrypolicyMikro/{id}', 'retrypolicyMikro');
    Route::get('/retrypolicy/{id}', 'retrypolicy');
    Route::get('/retryrenewal', 'retryrenewal');
    Route::get('/retryVa', 'retryVa');
    Route::get('/createCart', 'createCart');
    Route::get('/retryBackorder', 'retryBackorder');
    Route::get('/retryOfferingTravel', 'retryOfferingTravel');
});

// Callback
Route::post('/transaction/payment/callback', [TransactionController::class, 'callback']);
Route::post('/adira-callback', [AdiraController::class, 'callback']);
Route::get('/transaction/payment/success', [TransactionController::class, 'successPaid'])->name('xendit.success');
Route::get('/orders/handler-penutupan/{cartid}', [OrderController::class, 'checkstatus']);

Route::middleware([Tracking::class,'auth:api'])->group(function () {

    Route::controller(AdiraController::class)->group(function () {
        Route::post('/check-chassist', 'chassisExisting');
        Route::get('/get-province', 'getProvincy');
        Route::get('/get-city', 'getLocation');
        Route::post('/calculate-perluasan', 'calculatePerluasan');
    });

    Route::controller(OfferController::class)->prefix('offering')->group(function () {
        Route::get('/getInquiry/{id}', 'getInquiry')->name('offer.getInquiry');
        Route::post('/update/{id}', 'calculate')->name('offer.update');
        Route::post('/sendinqury', 'updateInquiry')->name('offer.inquiry');
        Route::post('/sendOfferingMail/{id}', 'sendOfferingMail')->name('offer.sendOfferingMail');
    });


    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('/recommendation', 'recommendation');
        // Route::get('/search', 'search');
    });
    Route::controller(CategoryController::class)->prefix('category')->group(function () {
        // Route::get('/', 'list');
        Route::get('/product-category/{id}', 'productCategory');
    });

    Route::controller(CalculateController::class)->prefix('calculate')->group(function () {
        Route::prefix('premi')->group(function () {
            Route::post('/mv', 'premiMv');
            Route::post('/moto', 'premiMoto');
        });
    });

    Route::controller(MVController::class)->prefix('mv')->group(function () {
        Route::get('/init', 'initMv');
        Route::post('/model', 'getModel');
        Route::post('/year', 'getyear');
        Route::post('/price', 'getprice');
        Route::post('/inquiry', 'sendInquiry');
        Route::get('/test', 'testA');
    });

    Route::controller(MotoController::class)->prefix('moto')->group(function () {
        Route::get('/init', 'initMoto');
        Route::post('/model', 'model');
        Route::post('/year', 'year');
        Route::post('/price', 'price');
        Route::post('/inquiry', 'sendInquiry');
        // Route::post('/calculate-moto-perluasan', 'calculateMotoPerluasan');
    });
    Route::post('/calculate-moto-perluasan', [MotoController::class, 'calculateMotoPerluasan']);
    
    Route::controller(TransactionController::class)->prefix('transaction')->group(function () {
        Route::get('/available', 'availPGMethod');
        Route::get('/init', 'initTransaction');
        Route::get('/total', 'total');
        Route::post('/post', 'sendTransaction');
        Route::get('/payment-method', 'paymentMethod');
    });

    Route::controller(HistoryController::class)->prefix('history')->group(function () {
        Route::get('/', 'history');
        Route::get('/comission', 'comission');
        Route::get('/orcomission', 'orcomission');
        Route::get('/duesoon', 'duesoon');
    });

    Route::controller(MikroController::class)->prefix('mikro')->group(function () {
        Route::post('/inquiry', 'addToCart');
    });

    Route::controller(OrderController::class)->prefix('order')->group(function () {
        Route::get('/init-form', 'getForm');
        Route::post('/store', 'post');
        Route::post('/delete/{id}', 'delete');
    });

    Route::controller(RenewalController::class)->prefix('renewal')->group(function () {
        Route::get('/init', 'init');
        Route::get('/getpolicy', 'listPolicyRenewal');
        Route::get('/get-form/{cart_id}', 'form_cart');
    });

    Route::controller(CartController::class)->prefix('cart')->group(function () {
        Route::get('/', 'list');
        Route::post('/add', 'create');
        Route::post('/remove', 'remove');
        Route::get('/detail/{id}', 'detail');
        Route::post('/offering/{id}', 'offering');
        Route::delete('/delete/{id}', 'deleteCart');
    });

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/get-data', 'getProfile');
        Route::post('/update', 'updateProfile');
        Route::post('/update-password', 'updatePassword');
        Route::post('/update-avatar', 'updateAvatar');
        Route::post('/update-fcm', 'updateFcm');
    });

    Route::controller(InboxController::class)->prefix('inbox')->group(function () {
        Route::get('/', 'getinboxuser');
        Route::get('/allinbox', 'all');
        Route::post('/create', 'create');
    });

    Route::controller(RefController::class)->prefix('referral')->group(function () {
        Route::get('/list-order', 'list_order');
        Route::get('/list-order/search', 'search_order');
        Route::get('/list-pickup', 'list_pickup');
        Route::get('/detail/{id}', 'detail');
        Route::post('/pickup', 'pickup');

        Route::prefix('product')->group(function () {
            Route::post('/create', 'post');
            Route::post('/update/{id}', 'update');
            // Route::get('/detail/{id}', 'detail');
        });
    });

    Route::controller(BackorderController::class)->prefix('backorder')->group(function () {
        Route::prefix('inquiry')->group(function () {
            Route::get('/getdata/{order_id}', 'getInquiry');
            Route::post('/calculate/{id}', 'calculate');
            Route::post('/send_inquiry', 'save_inquiry');
        });
        Route::get('/get-form/{id}', 'initForm');
        Route::post('/submit-data/{id}', 'submitForm');
    });

    Route::controller(BundleController::class)->prefix('bundle')->group(function () {
        Route::get('/', 'index');
    });

});
Route::controller(TravelController::class)->prefix('travel')->group(function () {
        Route::get('/', 'index')->name('travel.index');
        Route::get('/getCountry', 'getCountry')->name('travel.getcountry');
        Route::post('/getProduct', 'getProduct')->name('travel.getProduct');
        Route::post('/travelinfo', 'travellerinfo')->name('travel.info');
        Route::post('/submit-travellin', 'submitTraveller')->name('travel.submitTraveller');
        Route::post('/offering', 'offering')->name('travel.offering');
        Route::post('/get-coverage/{id}', 'getCoverage')->name('travel.getCoverage');
        Route::post('/offering/update/{id}', 'update_penawaran')->name('travel.updateoffering');
        Route::get('/offering/edit/{id}', 'edit_penawaran')->name('travel.editoffering');
        Route::get('/penutupan/{id}', 'penutupan')->name('travel.penutupan');
        Route::post('/penutupan/submit/{id}', 'submit_penutupan')->name('travel.submitpenutupan');
        Route::get('/error', 'error_page')->name('travel.errorpage');
        Route::post('/summary', 'summary',)->name('travel.summarypage');
    });
    
Route::controller(PerjalananController::class)->prefix('perjalanan')->group(function () {
        Route::get('/', 'index')->name('perjalanan.index');
        Route::post('/submit', 'submit')->name('perjalanan.submit');
        Route::get('/success', 'success')->name('perjalanan.success');
    });
Route::controller(PerjalananController::class)->prefix('perjalanan')->group(function () {
        Route::get('/', 'index')->name('perjalanan.index');
        Route::post('/submit', 'submit')->name('perjalanan.submit');
        Route::get('/success', 'success')->name('perjalanan.success');
    });

Route::get('/ref', [RegisterController::class, 'referral'])->name('referral');
Route::get('/ref_done', [RegisterController::class, 'referralDone'])->name('referralDone');