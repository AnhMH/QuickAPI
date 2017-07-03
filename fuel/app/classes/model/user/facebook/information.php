<?php
/**
 * Model_User_Facebook_Information - Model to operate to User_Facebook_Information's functions.
 * @package Model
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Model_User_Facebook_Information extends Model_Abstract
{
	protected static $_properties = array(
        'id',
        'user_id',
        'facebook_id',
        'facebook_name',
        'facebook_username',
        'facebook_email',
        'facebook_first_name',
        'facebook_last_name',
        'facebook_link',
        'facebook_image',
        'facebook_gender',
        'facebook_birthday',
        'created',
        'updated',
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

	protected static $_table_name = 'user_facebook_informations';

    /**
     * Get detail user facebook information.
     *
     * @author AnhMH
     * @param array $param Input data.
     * @return array Returns the array.
     */
    public static function get_detail($param)
    {
        $query = DB::select(
                array('users.id', 'user_id'),
                'users.email',
                self::$_table_name . '.id',  
                self::$_table_name . '.facebook_id',  
                self::$_table_name . '.facebook_name',  
                self::$_table_name . '.facebook_username',  
                self::$_table_name . '.facebook_first_name',  
                self::$_table_name . '.facebook_last_name',  
                self::$_table_name . '.facebook_link',  
                self::$_table_name . '.facebook_image',  
                self::$_table_name . '.facebook_gender'
            )
            ->from(self::$_table_name)
            ->join('users', 'LEFT')
            ->on('users.id', '=', self::$_table_name . '.user_id');
        if (!empty($param['id'])) {
            $query->where(self::$_table_name . '.id', '=', $param['id']);
        }
        if (!empty($param['user_id'])) {
            $query->where(self::$_table_name . '.user_id', '=', $param['user_id']);
        } 
        if (!empty($param['facebook_id'])) {
            $query->where(self::$_table_name .'.facebook_id', '=', $param['facebook_id']);
        }
        $data = $query->execute(self::$slave_db)->as_array();

        return !empty($data[0]) ? $data[0] : array();      
    }

    /**
     * Add or update info for user facebook information.
     *
     * @author AnhMH
     * @param array $param Input data.
     * @return int|bool Returns the integer or the boolean.
     */
    public static function add_update($param)
    {
        if (!empty($param['id'])) {
            $fbInformation = self::find($param['id']);
            if (empty($fbInformation)) {
                static::errorNotExist('id', $param['id']);
                return false;
            }
        }
        if (!empty($param['user_id'])) { 
            $options['where'] = array(
                'facebook_id' => $param['facebook_id'],
            );
            $fbInformation = self::find('first', $options);
        }
        if (empty($fbInformation)) {
            $fbInformation = new self;
        }
        if (!empty($param['user_id'])) {
            $fbInformation->set('user_id', $param['user_id']);
        }
        if (!empty($param['facebook_id'])) {
            $fbInformation->set('facebook_id', $param['facebook_id']);
        }
        if (!empty($param['facebook_name'])) {
            $fbInformation->set('facebook_name', $param['facebook_name']);
        }
        if (!empty($param['facebook_username'])) {
            $fbInformation->set('facebook_username', $param['facebook_username']);
        }
        if (!empty($param['facebook_email'])) {
            $fbInformation->set('facebook_email', $param['facebook_email']);
        }
        if (!empty($param['facebook_first_name'])) {
            $fbInformation->set('facebook_first_name', $param['facebook_first_name']);
        }
        if (!empty($param['facebook_last_name'])) {
            $fbInformation->set('facebook_last_name', $param['facebook_last_name']);
        }
        if (!empty($param['facebook_link'])) {
            $fbInformation->set('facebook_link', $param['facebook_link']);
        }
        if (!empty($param['facebook_image'])) {
            $fbInformation->set('facebook_image', $param['facebook_image']);
        }
        if (!empty($param['facebook_gender'])) {
            $fbInformation->set('facebook_gender', $param['facebook_gender']);
        }
        if (!empty($param['facebook_birthday'])) {
            $fbInformation->set('facebook_birthday', $param['facebook_birthday']);
        }
        if ($fbInformation->save()) {
            if (empty($fbInformation->id)) {
                $fbInformation->id = self::cached_object($fbInformation)->_original['id'];
            }
            return !empty($fbInformation->id) ? $fbInformation->id : 0;
        }
        return false;
    }
}
