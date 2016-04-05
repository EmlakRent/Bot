<?php
header('Access-Control-Allow-Origin: *');
ini_set('allow_url_fopen','1');
require 'vendor/autoload.php';
require 'functions.php';



Flight::route('GET /', function(){
    response('Hoşgeldin, get isteği kabul etmiyoruz lütfen konum bilgilerini post ile gönder!');
});

Flight::route('POST /', function(){

    # Get parameters
    list($latitude, $longitude, $street) = getParam();

    # Check parameters
    checkParam($latitude, $longitude);

    # Check street
    checkStreet($street, $latitude, $longitude);

    $street = paramConvertToUrl($street);

    $sonuc = array();

    $ilan_sayisi = 0;

    for ( $i = 0 ; $i < 50 ; $i = $i + 50)
    {
        $url = file_get_contents("http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=$street");

        //echo "<a href='$url'>$url</a><br>";

        preg_match_all('@<a class="classifiedTitle" href="(.*?)">(.*?)</a>@si',$url,$detay_icin_link);


        $ilan_sayisi = count($detay_icin_link[0]);

        for ( $j = 0; $j < $ilan_sayisi ; $j++ )
        {
            # Current url
            $url ="http://www.sahibinden.com".$detay_icin_link[1][$j];

            $sonuc[$j] = getDetail($url,$latitude,$longitude);
        }
    }


    response(sortNearestResult(removeFarResult($ilan_sayisi,$sonuc)));
});


Flight::start();