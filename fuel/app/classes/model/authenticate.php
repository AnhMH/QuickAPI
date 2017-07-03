<?php

/**
 * Any query in Model Authenticate.
 *
 * @package Model
 * @version 1.0
 * @author Le Tuan Tu
 * @copyright Oceanize INC
 */
class Model_Authenticate extends Model_Abstract {

    protected static $_properties = array(
        'id',
        'user_id',
        'token',
        'expire_date',
        'regist_type',
        'created',
    );
    
    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );
    
    protected static $_table_name = 'authenticates';
    
    /**
     * Check token.
     *
     * @author Le Tuan Tu
     * @param array $param Input data.	 
     * @return bool|array Returns the boolean or the array.	
     */
    public static function check_token() {
        return false;
    }

}
