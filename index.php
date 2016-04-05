<?php
header('Content-Type: application/json; charset=utf-8');
require 'vendor/autoload.php';
require 'functions.php';



Flight::route('GET /', function(){
    echo 'Hoşgeldin, get isteği kabul etmiyoruz lütfen konum bilgilerini post ile gönder!';
});

Flight::route('POST /', function(){

    $functions = new Functions();

    # Get parameters
    list($latitude, $longitude, $street) = $functions->getParam();

    # If latitude and longitude are empty
    if ( empty($latitude) && empty($longitude) )
    {
        echo "Konum bilgilerini göndermek zorundasın";
        exit;
    }

    # If street is empty
    if( empty($street)  )
    {

        # Use google reverse geocoding api and get street name.
        $street = $functions->getStreet($latitude, $longitude);

        # Return response
        $functions->response($street);
        exit;
    }

    # Cmp function
    function cmp($a,$b)
    {
        return strcmp($a['location']['distance'],$b['location']['distance']);
    }

    # Prepare results array
    $sonuc = array();
    $detay_icin_link = "";

    # Collect results
    list($ilan_sayisi, $sonuc) = $functions->collectResults($street, $detay_icin_link, $functions, $latitude, $longitude, $sonuc);


    # Remove far results
    $functions->removeFarResult($ilan_sayisi, $sonuc);

    # Sort by nearest results
    $data = $functions->sortNearestResult($sonuc);

    $functions->response($data);
});


Flight::start();