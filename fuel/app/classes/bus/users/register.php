<?php

namespace Bus;

/**
 * Register User
 *
 * @package Bus
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Users_Register extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array(
        'email',
        'password'
    );
    
    /** @var array $_email_format field email */
    protected $_email_format = array(
        'email'
    );

    /** @var array $_length Length of fields */
    protected $_length = array(
        'email'                 => array(1, 255),
        'password'              => array(4, 255),
        'name'                  => array(1, 64),
    );

    /** @var array $_number_format field number */
    protected $_number_format = array();

    /** @var array $_default_value field default */
    protected $_default_value = array();

    /**
     * Call function add_update() from model User
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_User::add_update($data);
            return $this->result(\Model_User::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
