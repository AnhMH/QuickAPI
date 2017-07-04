<?php

namespace Bus;

/**
 * User login via Twitter Token
 *
 * @package Bus
 * @created 2017-07-04
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Users_TwitterLogin extends BusAbstract {

    protected $_required = array(
        'oauth_token',
        'oauth_token_secret',
    );

    public function operateDB($data) {
        try {
            \Package::load('twitter');
            $result = \Model_User::login_twitter_by_token($data);
            if (!empty($result['id'])) {  
                $result['token'] = \Model_Authenticate::addupdate(array(
                    'user_id' => $result['id'],
                    'regist_type' => 'user'
                ));
            }
            $this->_response = $result;
            return $this->result(\Model_User::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }

}
