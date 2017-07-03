<?php

/**
 * Controller for actions on User
 *
 * @package Controller
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Controller_Users extends \Controller_App
{
    /**
     * Login User
     *
     * @return boolean
     */
    public function action_login()
    {
        return \Bus\Users_Login::getInstance()->execute();
    }
    
    /**
     * Register User
     *
     * @return boolean
     */
    public function action_register()
    {
        return \Bus\Users_Register::getInstance()->execute();
    }
}