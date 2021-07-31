<?php

Route::group(['namespace'=>'shurjopay\ShurjopayLaravelPackage\Http\Controllers'],function(){
	Route::get('shurjopay','ShurjopayController@index')->name('shurjopay');

	Route::post('shurjopay','ShurjopayController@send');
	//mowmita
    Route::get("response",'ShurjopayController@return')->name("response");
});

 
