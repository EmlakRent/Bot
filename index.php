<?php

require 'vendor/autoload.php';
require 'functions.php';



Flight::route('GET /', function(){
    echo 'Hoşgeldin, get isteği kabul etmiyoruz lütfen konum bilgilerini post ile gönder!';
});

Flight::route('POST /', function(){

    $functions = new Functions();

    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $street = $_POST["street"];

    if ( empty($latitude) && empty($longitude) )
    {
        echo "Konum bilgilerini göndermek zorundasın";
        exit;
    }

    if( empty($street)  )
    {


        # Use google reverse geocoding api and get street name.
        $street = $functions->getStreet($latitude, $longitude);


        $response = json_encode($street, JSON_UNESCAPED_UNICODE);
        echo $response;
        exit;
    }


    function cmp($a,$b)
    {
        return strcmp($a['location']['distance'],$b['location']['distance']);
    }


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

            $sonuc[$j] = $functions->getDetail($url,$latitude,$longitude);
        }
    }



    for ( $j = 0; $j < $ilan_sayisi ; $j++ )
    {

        if ( $sonuc[$j]['location']['distance'] > 1 or $sonuc[$j]['location']['distance'] == 0)
        {
            unset($sonuc[$j]);
        }
    }

    $data = [];

    foreach(array_values($sonuc) as $r)
    {
        $data[] = $r;
    }

    usort($data,'cmp');


    $return =  json_encode($data);
    echo $return;

    //echo 'I received a POST request.';
});


Flight::start();