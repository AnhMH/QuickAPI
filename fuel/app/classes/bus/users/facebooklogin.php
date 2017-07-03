<?php

namespace Bus;

/**
 * Facebook Login
 *
 * @package Bus
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Users_FacebookLogin extends BusAbstract {

    protected $_required = array(
        'token',
    );

    public function operateDB($data) {
        try {
            \Package::load('facebook');       
            $result = \Model_User::login_facebook_by_token($data);
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
