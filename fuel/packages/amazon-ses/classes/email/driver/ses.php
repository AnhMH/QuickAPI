<?php

/**
 * Amazon Email Delivery library for FuelPHP
 *
 * @package		Amazon
 * @version		1.1
 * @author		Rob McCann (rob@robmccann.co.uk)
 * @link		http://github.com/unforeseen/fuel-amazon-ses
 * 
 */

use LogLib\LogLib;

class Email_Driver_SES extends \Email_Driver {

    public $region = null;
    protected $debug = true;

    public function __construct($config = array()) {
        parent::__construct($config);
        \Config::load('ses', true);
        $this->region = \Config::get('ses.region', 'us-east-1'); //us-east-1 us-west-2
    }

    /**
     * Sends the email using the Amazon SES email delivery system
     * 
     * @return boolean	True if successful, false if not.
     */
    protected function _send() {
        $params = array(
            'Action' => 'SendEmail',
            'Source' => static::format_addresses(array($this->config['from'])),
            'Message.Subject.Data' => $this->subject,
            'Message.Body.Text.Data' => $this->alt_body,
            'Message.Body.Html.Data' => $this->body
        );

        $i = 0;
        foreach ($this->to as $value) {
            $params['Destination.ToAddresses.member.' . ($i + 1)] = static::format_addresses(array($value));
            ++$i;
        }

        $i = 0;
        foreach ($this->cc as $value) {
            $params['Destination.CcAddresses.member.' . ($i + 1)] = static::format_addresses(array($value));
            ++$i;
        }

        $i = 0;
        foreach ($this->bcc as $value) {
            $params['Destination.BccAddresses.member.' . ($i + 1)] = static::format_addresses(array($value));
            ++$i;
        }

        $i = 0;
        foreach ($this->reply_to as $value) {
            $params['ReplyToAddresses.member.' . ($i + 1)] = static::format_addresses(array($value));
            ++$i;
        }

        LogLib::info('Start send email', __METHOD__, $params);
        
        $date = date(DATE_RSS);
        $signature = $this->_sign_signature($date);
        
        LogLib::info('Signature: ', __METHOD__, $signature);
        
        try {
            $url = "tls://email-smtp.{$this->region}.amazonaws.com/";
            $curl = \Request::forge($url, array(
                        'driver' => 'curl',
                        'method' => 'post'
                    ))
                    ->set_header('Content-Type', 'application/x-www-form-urlencoded')
                    ->set_header(
                        'X-Amzn-Authorization', 
                        'AWS3-HTTPS AWSAccessKeyId=' . \Config::get('ses.access_key','AKIAJ5DQYYMFTZCYBR5A') . ', Algorithm=HmacSHA256, Signature=' . $signature
                    )
                    ->set_header('Date', $date);
            $response = $curl->execute($params); 
            
            LogLib::info('End send email', __METHOD__, $response->response());
            if (intval($response->response()->status / 100) != 2) {
                LogLib::info('return 1', __METHOD__, $response->response()->status);
                return false;
            }
            return true;
        }
        catch(\Exception $e)
        {
             LogLib::error(sprintf(
                    "Send mail Exception\n"
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
                __METHOD__, 
                $_FILES);
        }
        LogLib::info('return 2', __METHOD__, 'aaa');
        return false;
    }

    /**
     * Calculate signature
     * @param	string	date used in the header
     * @return	string 	RFC 2104-compliant HMAC hash
     */
    private function _sign_signature($date) {
        $hash = hash_hmac('sha256', $date, \Config::get('ses.secret_key','AsACdSqFXoca3e6MHwARA1c7yI7cIc10GmJU57Lt7UGr'), TRUE);
        return base64_encode($hash);
    }

}
