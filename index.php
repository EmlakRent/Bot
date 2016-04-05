<?php
header('Access-Control-Allow-Origin: *');
require 'vendor/autoload.php';
require 'functions.php';

function getUrlContent($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpcode>=200 && $httpcode<300) ? $data : false;
}

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
        $url = "http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=".$street;

        $url = getUrlContent($url);

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