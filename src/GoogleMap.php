<?php

// loading libs
//require_once 'Cache.php';
//require_once 'Curl.php';

/**
 * God-class to run Google Map queries
 * https://developers.google.com/maps/documentation/geocoding/
 */
class GoogleMapApi
{
    // results
    public $arrResponse = array();
    public $listObjects = array();

    public function __construct( )
    {
     
    }

    /**
     * Get coords from address location string
     * @param string $strLocation
     * @param string $strSensor
     * @return array
     */
    public function getCoords( $strLocation, $strSensor = 'true' )
    {
        
        $strLocation = str_replace(' ', '+', $strLocation);
        $strUrl = "http://maps.googleapis.com/maps/api/geocode/json?address=$strLocation&sensor=$strSensor";
        
        $arrResponse = $this->Curl($strUrl);
//        print_r($arrResponse);
        
        // no results
        if ($arrResponse['status'] == 'ZERO_RESULTS')
            return null;
        
        // found results - get coords from first result
        if ($arrResponse['status'] == 'OK') {
            if (isset($arrResponse['results'][0]['geometry']['location']['lat']) && isset($arrResponse['results'][0]['geometry']['location']['lng']))
                return $arrResponse['results'][0]['geometry']['location'];
        }
        
        // got api error
        if ($arrResponse['status'] != 'OK')
            throw new Exception( __FUNCTION__.': Google API status error: ['.$arrResponse['status'].']' );
        
        // no results found
        return null;
        
    }
    
    
    /**
     * Get api info by curl
     * @param string $strUrl
     * @return array
     */
    public function Curl( $strUrl )
    {

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        
        $output = curl_exec($ch);
        
        $nError = curl_errno($ch);
        $strError = curl_error($ch);
        
        curl_close($ch);

        if( $nError )
            throw new Exception( __FUNCTION__.': '.$strError );

        return json_decode($output, true);
        
    }    
    
    
}