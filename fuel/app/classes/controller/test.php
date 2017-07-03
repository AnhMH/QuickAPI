<?php

/**
 * Controller_Test
 *
 * @package Controller
 * @created 2016-08-31
 * @version 1.0
 * @author KienNH
 * @copyright Oceanize INC
 */
class Controller_Test extends \Controller_Rest {

    /**
     * 
     */
    public function action_index() {
        echo date('Y-m-d H:i:s');
        echo '<br/>';
        echo date_default_timezone_get();
        exit;
    }
    
    /**
     * Show PHP info
     */
    public function action_phpinfo() {
        phpinfo();
        exit;
    }
    
}
