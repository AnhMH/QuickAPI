<?php

use Fuel\Core\DB;
use Lib\Util;

/**
 * Any query in Model User
 *
 * @package Model
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Model_User extends Model_Abstract
{
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'password',
        'name',
        'email',
        'description',
        'image_path',
        'is_mail_check',
        'created',
        'updated',
        'disable'
    );
    
    protected static $_observers  = array(
        'Orm\Observer_CreatedAt' => array(
            'events'          => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events'          => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );

    /** @var array $_table_name name of table */
    protected static $_table_name = 'users';

    /**
     * Login User
     *
     * @author thailh
     * @param array $param Input data
     * @return array|bool Detail User or false if error
     */
    public static function get_login($param, $noCheckPassword = false)
    {
        \LogLib::info('Login', __METHOD__, $param);
        $query = DB::select(
                self::$_table_name.'.id',
                self::$_table_name.'.name',
                self::$_table_name.'.email',
                self::$_table_name.'.description',
                self::$_table_name.'.image_path',
                self::$_table_name.'.is_mail_check'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name . '.email', $param['email'])
        ;
        
        if ($noCheckPassword === false) {
            $query->where(self::$_table_name . '.password', Lib\Util::encodePassword($param['password'], $param['email']));
        }
        
        $login = $query->group_by(self::$_table_name . '.id')->execute()->offsetGet(0);
        
        if ($login) {
            if (empty($login['disable'])) {
                $login['token'] = Model_Authenticate::addupdate(array(
                    'user_id' => $login['id'],
                    'regist_type' => 'user'
                ));
                return $login;
            }
            static::errorOther(static::ERROR_CODE_OTHER_1, 'User is disabled');
            return false;
        }
        static::errorOther(static::ERROR_CODE_AUTH_ERROR, 'Email/Password');
        return false;
    }
}
