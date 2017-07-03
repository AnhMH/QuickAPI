<?php

/**
 * Support functions for Email
 *
 * @package Lib
 * @created 2014-11-25
 * @version 1.0
 * @author thailh
 * @copyright Oceanize INC
 */

namespace Lib;

use Fuel\Core\Config;

class Email {

    public static function beforeSend() {
        $send = Config::get('send_email', true);
        if ($send == false) {
            return false;
        }
        return true;
    }

    /**
     * Send email to test email (For testing)
     *
     * @author thailh
     * @param string email Email
     * @return string Real email | test email
     */
    public static function to($email) {
        $test_email = Config::get('test_email', '');
        return !empty($test_email) ? $test_email : $email;
    }
    
    /**
     * Send test
     *
     * @author thailh
     * @param array $param Information for sending email
     * @return bool Return true if successful ortherwise return false
     */
    public static function sendTest($param) {
        if (self::beforeSend() == false) {
            return true;
        }
        $to = !empty($param['to']) ? $param['to'] : '';
        if (empty($to)) {
            \LogLib::warning('Email is null or empty', __METHOD__, $to);
            return false;
        }
        $email = \Email::forge('jis');
        $email->from(Config::get('system_email.noreply'), '[Test] Bmaps No reply');
        $email->subject('Test at ' . date('Y-m-d H:i'));
        $body = 'This is message that sent from Bmaps.world.<br/><br/>';
        $email->html_body($body);
        $email->to(self::to($to));
        try {
            \LogLib::info("Resent email to {$to}", __METHOD__, $param);
            return $email->send();
        } catch (\EmailSendingFailedException $e) {
            \LogLib::warning($e, __METHOD__, $param);
            return false;
        } catch (\EmailValidationFailedException $e) {
            \LogLib::warning($e, __METHOD__, $param);
            return false;
        }
    }
    
}
