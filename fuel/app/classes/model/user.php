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
     * @author AnhMH
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
    
    /**
     * Add/Update User Info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool Detail User or false if error
     */
    public static function add_update($param)
    {
        $is_new = false;
        $id = !empty($param['id']) ? $param['id'] : 0;
        // check exist
        if (!empty($id)) {
            $user = self::find($id);
            if (empty($user)) {
                self::errorNotExist('user_id');
                return false;
            }
        } else {
            //check email if exist
            $option['where'] = array(
                'email' => $param['email']
            );
            $checkUserExist = self::find('first', $option);
            if (!empty($checkUserExist)) {
                \LogLib::info('Duplicate email in users', __METHOD__, $param);
                self::errorDuplicate('email', $param['email']);
                return false;
            }
            $is_new = true;
            $user = new self;
        }
        
        // Upload image
        if (!empty($_FILES)) {
            $uploadResult = \Lib\Util::uploadImage(); 
            if ($uploadResult['status'] != 200) {
                self::setError($uploadResult['error']);
                return false;
            }
            $param['image_path'] = $uploadResult['body'];
        }
        
        // set value
        $user->set('email', $param['email']);
        if (!empty($param['password'])) {
            $user->set('password', Lib\Util::encodePassword($param['password'], $param['email']));
        }
        if (isset($param['name'])) {
            $user->set('name', $param['name']);
        }
        if (isset($param['image_path'])) {
            $user->set('image_path', $param['image_path']);
        }
        if (isset($param['description'])) {
            $user->set('description', $param['description']);
        }
        // save to database
        if ($user->save()) {
            if (empty($user->id)) {
                $user->id = self::cached_object($user)->_original['id'];
            }
            return !empty($user->id) ? $user->id : 0;
        }
        return false;
    }
}
