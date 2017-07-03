<?php

/* 
 * Description: Contain global function
 * Author     : KienNH
 * Date       : 2015/12/18
 */

class helper {
    
    /**
     * Send notification for ios device
     * @param string $deviceToken
     * @param string $message
     * @param json $custom_data
     * @return boolean
     */
    public static function send_ios_notification($deviceToken, $message, $custom_data = NULL, &$error_response = NULL) {
        $error_response = NULL;
        
        // Validate params
        if (empty($deviceToken) || empty($message)) {
            $error_response = array(
                'code' => -1,
                'message' => 'Empty token or message'
            );
            return FALSE;
        } else if (strlen($deviceToken) != 64) {
            $error_response = array(
                'code' => -2,
                'message' => 'Invalid token'
            );
            return FALSE;
        }
        
        // Send
        $check = FALSE;
        \Config::load('push', true);
        \LogLib::info('APNS Start:', __METHOD__, $deviceToken);
        
        try {
            $ctx = stream_context_create();
            
            $env = \Fuel::$env;
            if ($env == 'production') {
                $pem_name = \Config::get('push.apns.local_cert');
                $SSL_URL  = 'ssl://gateway.push.apple.com:2195';
            } else {
                $pem_name = \Config::get('push.apns.local_cert_dev');
                $SSL_URL  = 'ssl://gateway.sandbox.push.apple.com:2195';
            }

            stream_context_set_option($ctx, 'ssl', 'local_cert', $pem_name);
            stream_context_set_option($ctx, 'ssl', 'passphrase', '');

            $fp = null;
            $i  = 0;

            while (!$fp && $i < 4) {
                // Open a connection to the APNS server
                $fp = stream_socket_client($SSL_URL, $errno, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
                
                /* This allows fread() to return right away when there are no errors.
                But it can also miss errors during last seconds of sending,
                as there is a delay before error is returned.
                Workaround is to pause briefly AFTER sending last notification,
                and then do one more fread() to see if anything else is there.*/
                stream_set_blocking ($fp, 0);
                $i++;
            }

            if ($fp) {
                // Create the payload body
                $body['aps'] = array(
                    'alert' => $message,
                    'badge' => 0,
                    'sound' => 'default'
                );

                if (!empty($custom_data)) {
                    $body['bmaps'] = json_encode($custom_data);
                }

                // Encode the payload as JSON
                $payload = json_encode($body);

                // Build the binary notification
                $msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack('n', strlen($payload)) . $payload;

                // Send it to the server
                $i = 0;
                $result = FALSE;
                while ($result === FALSE && $i < 4) {
                    // Open a connection to the APNS server
                    try {
                        $result = fwrite($fp, $msg, strlen($msg));
                    } catch (Exception $ex) {
                        
                    }
                    $i++;
                    
                    // Delay for resend
                    if ($result === FALSE && $i < 4) {
                        sleep(1); //sleep for 1 seconds
                    }
                }
                
                $check_response = self::checkAppleErrorResponse($fp);
                if ($check_response['code'] == 0) {
                    $check = TRUE;
                    $error_response = NULL;
                } else {
                    $error_response = $check_response;
                    
                }
            }
        } catch (Exception $ex) {
            $error_response = array(
                'code' => -3,
                'message' => $ex->getMessage()
            );
        }
        
        \LogLib::info('APNS End:', __METHOD__, $deviceToken);
        
        // Close socket
        try {
            fclose($fp);
        } catch (Exception $ex) {
            // Do nothing
        }
        
        return $check;
    }
    
    /**
     * Check error from Apple response
     * @param type $fp
     * @return array(code, message)
     */
    public static function checkAppleErrorResponse($fp) {
        // Default return OK
        $ret = array(
            'code' => 0,
            'message' => 'No errors encountered'
        );
        
        // byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
        // NOTE: Make sure you set stream_set_blocking($fp, 0)
        // or else fread will pause your script and wait forever when there is no response to be sent.
        $apple_error_response = fread($fp, 6);
        
        if ($apple_error_response) {
            //unpack the error response (first byte 'command" should always be 8)
            $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);
            
            $ret['code'] = $error_response['status_code'];
            
            if ($error_response['status_code'] == 0) {
                $ret['message'] = 'No errors encountered';
            } else if ($error_response['status_code'] == 1) {
                $ret['message'] = 'Processing error';
            } else if ($error_response['status_code'] == 2) {
                $ret['message'] = 'Missing device token';
            } else if ($error_response['status_code'] == 3) {
                $ret['message'] = 'Missing topic';
            } else if ($error_response['status_code'] == 4) {
                $ret['message'] = 'Missing payload';
            } else if ($error_response['status_code'] == 5) {
                $ret['message'] = 'Invalid token size';
            } else if ($error_response['status_code'] == 6) {
                $ret['message'] = 'Invalid topic size';
            } else if ($error_response['status_code'] == 7) {
                $ret['message'] = 'Invalid payload size';
            } else if ($error_response['status_code'] == 8) {
                $ret['message'] = 'Invalid token';
            } else if ($error_response['status_code'] == 255) {
                $ret['message'] = 'None (unknown)';
            } else {
                $ret['message'] = 'Not listed';
            }
        }
        
        return $ret;
    }

    /**
     * Send Android notification
     * @param array $google_regid
     * @param string $message
     * @param array $custom_data
     * @return boolean
     */
    public static function send_android_notification($google_regid, $message, $custom_data = NULL, &$error_type = NULL) {
        $error_type = NULL;
        
        // Validate params
        if (empty($google_regid) || (empty($message) && empty($custom_data))) {
            return FALSE;
        }
        
        // Prepare
        \Config::load('push', true);
        if (!is_array($google_regid)) {
            $google_regid = array($google_regid);
        }
        if (empty($custom_data)) {
            $custom_data = array();
        }
        if (empty($message)) {
            $message = '';
        }
        
        // Init
        $url = 'https://android.googleapis.com/gcm/send';
        $headers = array(
            'Authorization: key=' . \Config::get('push.gcm.authorization_key'),
            'Content-Type: application/json'
        );
        $max_message_length = 1024 * 4; // 4kb
        $total_len = 0;
        $bSent = TRUE;
        
        // Split message
        foreach ($custom_data as $key => $value) {
            $total_len += strlen($key) + strlen($value);
        }
        
        if (strlen($message) > $max_message_length - $total_len - 7) {// length of "message" = 7
            $message = self::get_substr_message($message, $max_message_length - $total_len - 7);
        }
        $custom_data['message'] = $message;
        
        $fields = array(
            'registration_ids' => $google_regid,
            'data' => $custom_data,
        );

        // Send
        try {
            \LogLib::info('GCM Start:', __METHOD__, $fields);

            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            // Execute post
            $jsonResponse = curl_exec($ch);
            \LogLib::info("GCM End:", __METHOD__, $jsonResponse);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            if (empty($errno) && $httpcode == 200) {
                $result = json_decode($jsonResponse, true);
                
                if (!empty($result['results'])) {
                    foreach ($result['results'] as $key => $values) {
                        foreach ($values as $error_code => $value) {
                            if ($error_code == 'error') {
                                $error_type = $value;
                                $bSent = FALSE;
                                \LogLib::info("GCM End: Error:", __METHOD__, array(
                                    'registration_ids' => $google_regid,
                                    'error' => $value
                                ));
                            }
                        }
                    }
                }
            } else {
                $bSent = FALSE;
                $error = curl_error($ch);
                \LogLib::info("GCM End: Error:", __METHOD__, $error);
            }
            
            // Close connection
            try {
                curl_close($ch);
            } catch (Exception $ex) {}
        } catch (Exception $ex) {
            \LogLib::error(sprintf("GCM Exception\n"
                                . " - Message : %s\n"
                                . " - Code : %s\n"
                                . " - File : %s\n"
                                . " - Line : %d\n"
                                . " - Stack trace : \n"
                                . "%s", 
                                $ex->getMessage(), 
                                $ex->getCode(), 
                                $ex->getFile(), 
                                $ex->getLine(), 
                                $ex->getTraceAsString()), 
                __METHOD__, $fields);
            $bSent = FALSE;
        }
        
        return $bSent;
    }
    
    /**
     * Sub string
     * @param type $str
     * @param type $limit_range
     * @return string
     */
    public static function get_substr_message($str, $limit_range){
        $dot            = '･･･';
        $sublimit_range = $limit_range - strlen(json_encode($dot)) + 2;         
        $arr1           = self::mbStringToArray($str);          
        $element_stop   = self::get_break_point($arr1, $sublimit_range);
        
        if ($element_stop){
           $arr_need = mb_substr($str, 0, $element_stop, 'UTF-8') . $dot;
           return $arr_need;
        } else {
            return $str;
        }
    }
    
    /**
     * 
     * @param type $array_str
     * @param type $at_bytes
     * @return int
     */
    public static function get_break_point($array_str, $at_bytes) {
        $length_string = 0;
        $i = 0;

        foreach ($array_str as $char) {
            $length_string += (strlen(json_encode($char)) - 2);
            if ($length_string > $at_bytes) {
                return $i;
            }
            $i++;
        }
    }

    /**
     * string to array
     * @param string $string
     * @return array
     */
    public static function mbStringToArray($string) {
        $strlen = mb_strlen($string);

        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "UTF-8");
            $string = mb_substr($string, 1, $strlen, "UTF-8");
            $strlen = mb_strlen($string);
        }

        return $array;
    }
    
}
