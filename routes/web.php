<?php

use Illuminate\Support\Facades\Route;


Route::any('handler', 'DonationController@submitDonation')->name('handler');


Route::post('donation/store', 'DonationController@submitDonation')->name('donation.store');
Route::post('finish', 'DonationController@handler')->name('donation.finish');
Route::get('getsnap/{amount?}', 'MidtransController@getSnap')->name('donation.getsnap');
Route::post('notification/handler', 'DonationController@notificationHandler')->name('notification.handler');

