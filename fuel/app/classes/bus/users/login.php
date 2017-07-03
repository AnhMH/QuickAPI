<?php

namespace Bus;

/**
 * Login User
 *
 * @package Bus
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Users_Login extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array(
        'email',
        'password'
    );

    /** @var array $_length Length of fields */
    protected $_length = array(
        'email'    => array(1, 255),
        'password' => array(6, 40)
    );

    /** @var array $_email_format field email */
    protected $_email_format = array(
        'email'
    );

    /**
     * Call function get_login() from model User
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_User::get_login($data);
            return $this->result(\Model_User::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
