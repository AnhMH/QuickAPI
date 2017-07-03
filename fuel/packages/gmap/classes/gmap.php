<?php

/**
 * Google Map API
 *
 * @package    Gmap
 * @version    1.0
 * @author     ThaiLH
 * @copyright  OCEANIZE, Inc
 */

namespace Gmap;

class Gmap {
    
    /** @var array $_placeUrl array url */
    private static $_placeUrl = array(
        'search' => 'https://maps.googleapis.com/maps/api/place/nearbysearch/json',
        'searchText' => 'https://maps.googleapis.com/maps/api/place/textsearch/json',
        'detail' => 'https://maps.googleapis.com/maps/api/place/details/json',
        'photo' => 'https://maps.googleapis.com/maps/api/place/photo',
        'add' => 'https://maps.googleapis.com/maps/api/place/add/json',
        'autocomplete' => 'https://maps.googleapis.com/maps/api/place/autocomplete/json',
        'geocode' => 'https://maps.googleapis.com/maps/api/geocode/json',
    );
    
    private static $_cacheKey = 'gmap_key';
            
    /**
    * Call api request 
    *
    * @author thailh
    * @param string $url Request url.
    * @param array $param Input data.
    * @param string $method Method GET|POST
    * @return array|bool Response data or false if error
    */
    public static function call($url, $param = array(), $method = 'GET') {
        try { 
            \LogLib::info('[Gmap] URL:', __METHOD__, $url);   
            \LogLib::info('[Gmap] START:', __METHOD__, $param); 
            $ch = curl_init();     
            $options = array(                
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,              
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SAFE_UPLOAD => false,
                CURLOPT_TIMEOUT => \Config::get('gmap.timeout', 30),
            );
            list($keyIndex, $key) = self::get_key_data(); 
            if ($method == 'GET') {
                $param['key'] = $key;
                $url .= '?' . http_build_query($param);
            } elseif ($method == 'POST') {     
                $url .= '?key=' . $key;
                $options[CURLOPT_HTTPHEADER] = array('Content-Type: application/json');
                $options[CURLOPT_POST] = true;                 
                $options[CURLOPT_POSTFIELDS] = json_encode($param);
                $options[CURLOPT_FOLLOWLOCATION] = true;
                $options[CURLOPT_VERBOSE] = true;
            }      
            $options[CURLOPT_URL] = $url; 
            curl_setopt_array($ch, $options); 
            $jsonResponse = curl_exec($ch);
            $response = json_decode($jsonResponse, true);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            curl_close($ch);
            if (isset($response['status']) && $response['status'] == 'ZERO_RESULTS') {
                $response['status'] = 'OK';
            }
            // change to next key
            if ($response['status'] == 'OVER_QUERY_LIMIT' || $response['status'] == 'REQUEST_DENIED') {
                \LogLib::error("GMAP key expired (" . $response['status'] . "): " . $key);                
                $keys = \Config::get('gmap.keys');
                $keyIndex = ($keyIndex == count($keys) - 1 ? 0 : $keyIndex + 1);              
                \Lib\Cache::set(static::$_cacheKey, array($keyIndex, $keys[$keyIndex]), 24*60*60);                
            }
            if (empty($errno) && $httpcode == 200 && isset($response['status']) && $response['status'] == 'OK') {
                return $response;
            }
            if (isset($response['error_message'])) {
                $message = $response['error_message'];
            } elseif (isset($response['status'])) {
                $message = $response['status'];
            } else {
                $message = 'System error';
            }
            throw new \Exception($message, 500);           
        } catch (\Exception $e) {
             \LogLib::error(sprintf("GMAP Exception\n"
                            . " - Message : %s\n"
                            . " - Code : %s\n"
                            . " - File : %s\n"
                            . " - Line : %d\n"
                            . " - Stack trace : \n"
                            . "%s",
                            $e->getMessage(), 
                            $e->getCode(), 
                            $e->getFile(), 
                            $e->getLine(), 
                            $e->getTraceAsString()), 
            __METHOD__, $param);
            return false;
        }
    }
     
    public static function get_key_data() {       
        $data = \Lib\Cache::get(static::$_cacheKey);
        if ($data === false) {
            $data = array(0, \Config::get('gmap.keys')[0]);           
            \Lib\Cache::set(static::$_cacheKey, $data, 24*60*60);
        }  
        return $data;
    }
    
    /**
     * Get the url that the provided URL redirects to
     *
     * @author thailh
     * @param string $url Url
     * @return string Real url
     */
    public static function get_real_url($url) {
        
        $ch = curl_init();    
        $options = array(      
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,              
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SAFE_UPLOAD => false,
            CURLOPT_FOLLOWLOCATION => false,            
            CURLOPT_TIMEOUT => \Config::get('gmap.timeout', 30),
        );     
        curl_setopt_array($ch, $options); 
        curl_exec($ch);
        $result = curl_getinfo($ch);
        curl_close($ch);
        return !empty($result['redirect_url']) ? $result['redirect_url'] : '';
    }

    /**
     * Search places on google map
     *
     * @author thailh
     * @param array $param Input data.
     * @return array|bool Places info or false if error
     */
    public static function search_place($param = array(), $textSearch = false) {
        if (empty($param['location'])) {
            \LogLib::info('Invalid parameters', __METHOD__, $param);
            return false;
        }
        $search_param = array(
            'sensor', // Required parameters
            'pagetoken',  // Optional parameters
            'types',
            'rankby',
            'language',
            'minprice',
            'maxprice',
            'name',
            'opennow',
            'zagatselected',
        );
        
        if ($textSearch) {
            // Text search
            $search_param[] = 'query';
        } else {
            // Nearby search
            $search_param[] = 'location';
            $search_param[] = 'radius';
            $search_param[] = 'keyword';
        }
        
        $param['sensor'] = 'true';
        if (!isset($param['radius'])) {
            $param['radius'] = \Config::get('gmap.radius', 1000); 
        }
        if (isset($param['next_page_token']) && !isset($param['pagetoken'])) {
            $param['pagetoken'] = $param['next_page_token'];
            unset($param['next_page_token']);
        }    
        if (!empty($param['language_type'])) {
            $param['language'] = \Config::get('gmap.language')[$param['language_type']]; 
        }
        foreach ($param as $key => $value) {
            if (!in_array($key, $search_param)) {
                unset($param[$key]);
            }
        }
        
        if ($textSearch) {
            // Text search
            $url = static::$_placeUrl['searchText'];
        } else {
            // Nearby search
            $url = static::$_placeUrl['search'];
        }
        
        $data = self::call($url, $param);               
        return $data;
    }
    
    /**
     * autocomplete
     *
     * @author quan
     * @param array $param Input data.
     * @return array|bool Places info or false if error
     */
    public static function autocomplete($param = array()) {
        if (empty($param['input'])) {
            \LogLib::info('Invalid parameters', __METHOD__, $param);
            return false;
        }
        $search_param = array(
            'input',
            'location',
            'language',
            'radius'
        );
        if (!empty($param['language_type'])) {
            $param['language'] = \Config::get('gmap.language')[$param['language_type']]; 
        }
        foreach ($param as $key => $value) {
            if (!in_array($key, $search_param)) {
                unset($param[$key]);
            }
        }
        $url = static::$_placeUrl['autocomplete'];
        $data = self::call($url, $param);               
        return $data;
    }
    
    /**
     * Get a Place info on google map
     *
     * @author thailh
     * @param array $param Input data.
     * @return array|bool Place info or false if error
     */
    public static function get_place_detail($param = array()) {
        if (empty($param['google_place_id']) && empty($param['reference'])) {
            \LogLib::info('Invalid parameters', __METHOD__, $param);
            return false;
        }
        $url = static::$_placeUrl['detail'];
        $apiParam = array();
        if (!empty($param['google_place_id'])) {
            $apiParam['placeid'] = $param['google_place_id'];
        }
        if (!empty($param['reference'])) {
            $apiParam['reference'] = $param['reference'];
        }
        if (!empty($param['location'])) {
            $apiParam['location'] = $param['location'];
        }       
        if (!empty($param['language_type'])) {
            $apiParam['language'] = \Config::get('gmap.language')[$param['language_type']]; 
        }
        $data = self::call($url, $apiParam);
        if ($data !== false && !empty($data['result'])) {
            $data = $data['result'];
        }
        if (empty($data['place_id'])
            || empty($data['types'])
            || empty($data['geometry']['location']['lat'])
            || empty($data['geometry']['location']['lng'])
            || empty($data['name'])) {
            \LogLib::warning("Invalid google place detail information", __METHOD__, $data);
            return false;
        }
        if (!empty($data['photos'])) {
            $data['photo_url'] = array();           
            foreach ($data['photos'] as &$photo) {
                $data['photo_url'][] = array(
                    'image_path' => self::get_place_photo(array(
                                        'maxwidth' => $photo['width'],
                                        'photoreference' => $photo['photo_reference']
                                    )
                                ),
                    'thm_image_path' => self::get_place_photo(array(
                                        'maxwidth' => 200,
                                        'photoreference' => $photo['photo_reference']
                                    )
                                ),
                );                             
            }
            unset($data['photos']);
        }
        return $data;
    }

    /**
     * Get photo url
     *
     * @author thailh
     * @param array $param Input data.
     * @return string Photo url
     */
    public static function get_place_photo($param = array(), $get_real_url = true) {
        if (empty($param['photoreference'])) {
            \LogLib::info('Invalid parameters', __METHOD__, $param);
            return false;
        }     
        list($keyIndex, $key) = self::get_key_data();
        $maxwidth = !empty($param['maxwidth']) ? $param['maxwidth'] : 400;
        $url = static::$_placeUrl['photo'] . '?' . http_build_query(array(
                'key' => $key,
                'maxwidth' => $maxwidth,
                'photoreference' => $param['photoreference'],
            )
        );
        if ($get_real_url === true) {
            $url = self::get_real_url($url);
        }
        return $url;
    }
    
    /**
     * Add a Place to google map
     *
     * @author thailh
     * @param array $param Input data.
     * @return array|bool Place info or false if error
     */
    public static function add_place($param = array()) {
        if (empty($param['location'])) {
            $param['location'] = '';
        }
        $location = explode(',', $param['location']);
        if (count($location) !== 2) {
            \LogLib::info('Invalid parameters', __METHOD__, $param);
            return false;
        }
        $addPlaceParam = array(
            'location' => array(
                'lat' => floatval($location[0]),
                'lng' => floatval($location[1]),
            ),         
        );
        if (!empty($param['name'])) {
            $addPlaceParam['name'] = $param['name'];
        }
        if (!empty($param['accuracy'])) {
            $addPlaceParam['accuracy'] = $param['accuracy'];
        }
        if (!empty($param['types'])) {
            if (is_string($param['types'])) {
                $param['types'] = explode(',', $param['types']);
            }
            $addPlaceParam['types'] = $param['types'];
        }
        if (!empty($param['address'])) {
            $addPlaceParam['address'] = $param['address'];
        }
        if (!empty($param['phone_number'])) {
            $addPlaceParam['phone_number'] = $param['phone_number'];
        }
        if (!empty($param['website'])) {
            $addPlaceParam['website'] = $param['website'];
        }
        if (!empty($param['language'])) {
            $addPlaceParam['language'] = $param['language'];
        }
        if (!empty($param['language_type'])) {
            $addPlaceParam['language'] = \Config::get('gmap.language')[$param['language_type']]; 
        }
        $url = static::$_placeUrl['add'];
        $data = self::call($url, $addPlaceParam, 'POST');        
        return $data;
    }
    
    /**
     * Get Postcode from Lat/Lng
     * 
     * @param float $lat
     * @param float $lng
     * @return string
     */
    public static function get_postcode_from_latlng($lat, $lng, $clear_format = true) {
        $postcode = '';
        $lat = floatval($lat);
        $lng = floatval($lng);
        
        if (empty($lat) || empty($lng)) {
            return $postcode;
        }
        
        $param = array(
            'latlng' => $lat . ',' . $lng
        );
        
        $url = static::$_placeUrl['geocode'];
        for ($i = 0; $i < 3; $i++) {// Retry 3 time
            $data = self::call($url, $param);
            if (!empty($data)) {
                break;
            } else {
                sleep(1);// Stop 1s for next request
            }
        }
        
        if (!empty($data['results'])) {
            $found = false;
            foreach ($data['results'] as $result) {
                if ($found) {
                    break;
                }
                if (!empty($result['address_components'])) {
                    foreach ($result['address_components'] as $address_component) {
                        if ($found) {
                            break;
                        }
                        if (!empty($address_component['types'])) {
                            foreach ($address_component['types'] as $type) {
                                if ($type == 'postal_code') {
                                    $postcode = $address_component['long_name'];
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (!empty($postcode) && $clear_format) {
            $postcode = str_replace('-', '', $postcode);
        }

        return $postcode;
    }
}
