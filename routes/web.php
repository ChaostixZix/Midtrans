<?php

use Illuminate\Support\Facades\Route;


Route::any('handler', 'DonationController@submitDonation')->name('handler');


Route::post('donation/store', 'MidtransController@submitDonation')->name('donation.store');
Route::post('finish', 'MidtransController@handler')->name('donation.finish');
Route::get('getsnap/{amount?}/{plan?}', 'MidtransController@getSnap')->name('donation.getsnap');
Route::post('notification/handler', 'MidtransController@handler')->name('notification.handler');

