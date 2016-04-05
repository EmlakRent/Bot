<?php
/**
 * Created by PhpStorm.
 * User: cagatay
 * Date: 05/04/16
 * Time: 18:56
 */
###############################################################################################################

    function cmp($a,$b)
    {
        return strcmp($a['location']['distance'],$b['location']['distance']);
    }

    /**
     * @param $latitude
     * @param $longitude
     */
    function checkParam($latitude, $longitude)
    {
    # If latitude and longitude are empty
        if (empty($latitude) && empty($longitude)) {
            response("Konum bilgilerini göndermek zorundasın");
        }
    }

    /**
     * @param $street
     * @return string
     */
    function paramConvertToUrl($street)
    {
        $street = implode("+", explode(" ", $street));
        return $street;
    }

    /**
     * @param $street
     * @param $latitude
     * @param $longitude
     */
    function checkStreet($street, $latitude, $longitude)
    {
    # If street is empty
        if (empty($street)) {

            # Use google reverse geocoding api and get street name.
            $street = getStreet($latitude, $longitude);



            # Return response
            response($street);
        }
    }

    /**
     * @param $ilan_sayisi
     * @param $sonuc
     */
    function removeFarResult($ilan_sayisi, $sonuc)
    {
        for ($j = 0; $j < $ilan_sayisi; $j++) {

            if ($sonuc[$j]['location']['distance'] > 1 or $sonuc[$j]['location']['distance'] == 0) {
                unset($sonuc[$j]);
            }
        }

        return $sonuc;
    }
    /**
     * @param $sonuc
     * @return array
     */
    function sortNearestResult($sonuc)
    {
        $data = [];

        foreach (array_values($sonuc) as $r) {
            $data[] = $r;
        }

        usort($data, 'cmp');
        return $data;
    }

    /**
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @param $unit
     * @return float
     */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * @param $latitude
     * @param $longitude
     */
    function getStreet($latitude, $longitude)
    {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&sensor=true";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);


        return $jsondata['results']['0']['address_components']['2']['long_name'];
    }


    /**
     * @param $site
     * @return array
     */
    function getLocation($site,$latitude,$longitude) # Current locations
    {
        preg_match_all('@<div id="gmap" data-lat="(.*?)" data-lon="(.*?)" data-lang="tr"></div>@si', $site, $lokasyon);

        unset($lokasyon[0]);

        $lokasyon = array_values($lokasyon);

        foreach( $lokasyon as $key => $value )
        {
            if( empty( $value[0] ) )
            {
                unset( $lokasyon[$key] );
            }
        }
        if( empty( $lokasyon ) )
        {
            $konum["latitude"] = 0;
            $konum["longitude"] = 0;
            $konum["distance"] = 0;
        }
        else
        {
            /**
             * Detail locations
             */
            $lat = $lokasyon[0][0];
            $lon = $lokasyon[1][0];

            $konum["latitude"] = $lat;
            $konum["longitude"] = $lon;
            $konum["distance"] = distance($lat,$lon,$latitude,$longitude,"K");

        }

        return $konum;
    }

    /**
     * @param $site
     * @return mixed
     */
    function getPrice($site)
    {
        preg_match_all('@<h3>(.*?)</h3>@si', $site, $fiyat);

        $string = trim(preg_replace('/\s\s+/', ' ', strip_tags($fiyat[0][0])));
        return $string;
    }

    /**
     * @param $site
     * @return mixed
     */
    function getDescription($site)
    {
        preg_match_all('@<div id="classifiedDescription" class="uiBoxContainer">(.*?)</div>@si', $site, $aciklama);

        //$aciklama = validate($aciklama[0][0]);

        return strip_tags($aciklama);
    }

    /**
     * @param $site
     * @return mixed
     */
    function getPhoto($site)
    {
        preg_match_all('@<img width="480" height="360" src="(.*?)" alt="(.*?)">@si', $site, $resimler);
        return $resimler;
    }

    /**
     * @param $url
     * @return array
     */
    function getDetail($url,$latitude,$longitude)
    {
        $result = array();

//        $site = $this->getUrlContent($url);

        $site = file_get_contents($url);
//        $site = $this->file_get_contents_curl($url);
        //$site = $this->getContent($url);

        //echo $url;

        $result["url"]   = $url;
        $result["location"] = getLocation($site,$latitude,$longitude);
        $result["price"]  = getPrice($site);

        return $result;
    }

    /**
     * @return array
     */
    function getParam()
    {
        $latitude = $_POST["latitude"];
        $longitude = $_POST["longitude"];
        $street = $_POST["street"];
        return array($latitude, $longitude, $street);
    }

    /**
     * @param $data
     */
    function response($data)
    {
        $return = json_encode($data,JSON_UNESCAPED_UNICODE);
        echo $return;
        exit;
    }
