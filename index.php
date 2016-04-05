<?php
header('Access-Control-Allow-Origin: *');
ini_set('allow_url_fopen','1');
require 'vendor/autoload.php';
require 'functions.php';



Flight::route('GET /', function(){
    echo 'Hoşgeldin, get isteği kabul etmiyoruz lütfen konum bilgilerini post ile gönder!';
});

Flight::route('POST /', function(){

    # Get parameters
    list($latitude, $longitude, $street) = getParam();


    # If latitude and longitude are empty
    if ( empty($latitude) && empty($longitude) )
    {
        response("Konum bilgilerini göndermek zorundasın");
    }

    # If street is empty
    if( empty($street)  )
    {

        # Use google reverse geocoding api and get street name.
        $street = getStreet($latitude, $longitude);

        # Return response
        response($street);
    }


    $ilan_sayisi = 0;
    $sonuc = array();


    response("Burada sana yakınındaki sonuçları döndüreceğim.");

});


Flight::start();