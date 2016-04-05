<?php
header('Access-Control-Allow-Origin: *');
ini_set('allow_url_fopen','1');
require 'vendor/autoload.php';
require 'functions.php';



Flight::route('GET /', function(){
    echo 'Hoşgeldin, get isteği kabul etmiyoruz lütfen konum bilgilerini post ile gönder!';
});

Flight::route('POST /', function(){

    $file = "http://cagataycali.xyz";
    if (function_exists('curl_version'))
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $file);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);
    }
    else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
    {
        $content = file_get_contents($file);
    }
    else
    {
        echo 'You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!';
    }

    print_r($content);
    echo "Herşey yolunda!";
    exit;


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


    $ilan_sayisi = 0;
    $sonuc = array();


    for ( $i = 0 ; $i < 50 ; $i = $i + 50)
    {
        //$url=file_get_contents("http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=".urlencode($street));
        $url = $functions->file_get_contents_curl("http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=$street");

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

        if ( $sonuc[$j]['location']['distance'] > 1  or $sonuc[$j]['location']['distance'] == 0 )
        {
            unset($sonuc[$j]);
        }
    }

    $data = [];

    foreach(array_values($sonuc) as $r)
    {
        $data[] = $r;
    }

    function cmp($a,$b)
    {
        return strcmp($a['location']['distance'],$b['location']['distance']);
    }

    usort($data,'cmp');


//    print_r($data);
    $return =  json_encode($data);
//    echo "Test";
    echo $return;

});


Flight::start();