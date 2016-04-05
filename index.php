<?php
header('Access-Control-Allow-Origin: *');
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

    $sonuc = array();

    $ilan_sayisi = 0;

# todo : mbdef class'ına sahip divi bul ve kaç sayfa olduğunu tespit et && infoSearchResults
    for ( $i = 0 ; $i < 50 ; $i = $i + 50)
    {
        $url = file_get_contents("http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=".$street);

        //echo "<a href='$url'>$url</a><br>";

        preg_match_all('@<a class="classifiedTitle" href="(.*?)">(.*?)</a>@si',$url,$detay_icin_link);


        $ilan_sayisi = count($detay_icin_link[0]);

        for ( $j = 0; $j < $ilan_sayisi ; $j++ )
        {
            # Current url
            $url ="http://www.sahibinden.com".$detay_icin_link[1][$j];

            $sonuc[$j] = $functions->getDetail($url,$latitude,$longitude);
        }
    }
    # Remove far results
    $sonuc = $functions->removeFarResult($ilan_sayisi, $sonuc);

    # Sort by nearest results
    $data = $functions->sortNearestResult($sonuc);

    $functions->response($data);
});


Flight::start();