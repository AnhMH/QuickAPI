<?php
/**
 * Model_User_Twitter_Information - Model to operate to User_Twitter_Information's functions.
 * @package Model
 * @version 1.0
 * @author Le Tuan Tu
 * @copyright Oceanize INC
 */
class Model_User_Twitter_Information extends Model_Abstract
{
	protected static $_properties = array(
        'id',
        'user_id',
        'tw_id',
        'tw_name',
        'tw_screen_name',
        'tw_description',
        'tw_url',
        'tw_lang',
        'tw_profile_image_url',
        'tw_profile_image_url_https',
        'created',
        'updated',
        'tw_email',
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

	protected static $_table_name = 'user_twitter_informations';

    /**
     * Get detail user facebook information.
     *
     * @author Caolp
     * @param array $param Input data.
     * @return array Returns the array.
     */
    public static function get_detail($param)
    {
        $query = DB::select(
            array('users.id', 'user_id'),
            self::$_table_name . '.tw_id',
            self::$_table_name . '.tw_name',
            self::$_table_name . '.tw_screen_name',
            self::$_table_name . '.tw_description',
            self::$_table_name . '.tw_url',
            self::$_table_name . '.tw_lang',
            self::$_table_name . '.tw_profile_image_url',
            self::$_table_name . '.tw_profile_image_url_https',
            self::$_table_name . '.created',
            self::$_table_name . '.updated'
        )
            ->from('users')
            ->join(self::$_table_name, 'LEFT')
            ->on('users.id', '=', self::$_table_name . '.user_id');
        if (!empty($param['id'])) {
            $query->where(self::$_table_name . '.id', '=', $param['id']);
        }
        if (!empty($param['user_id'])) {
            $query->where(self::$_table_name . '.user_id', '=', $param['user_id']);
        } 
        if (!empty($param['tw_id'])) {
            $query->where('tw_id', '=', $param['tw_id']);
        }
        $data = $query->execute(self::$slave_db)->as_array();

        return !empty($data[0]) ? $data[0] : array();      
    }

    /**
     * Add or update info for user facebook information.
     *
     * @author Le Tuan Tu
     * @param array $param Input data.
     * @return int|bool Returns the integer or the boolean.
     */
    public static function add_update($param)
    {
        if (!empty($param['id'])) {
            $twInformation = self::find($param['id']);
            if (empty($twInformation)) {
                static::errorNotExist('id', $param['id']);
                return false;
            }
        }
        if (!empty($param['user_id'])) { 
            $options['where'][] = array(
                'tw_id' => $param['tw_id'],
            );
            $twInformation = self::find('first', $options);
        }
        if (empty($twInformation)) {
            $twInformation = new self;
        }
        if (!empty($param['user_id'])) {
            $twInformation->set('user_id', $param['user_id']);
        }
        if (!empty($param['tw_id'])) {
            $twInformation->set('tw_id', $param['tw_id']);
        }
        if (!empty($param['tw_name'])) {
            $twInformation->set('tw_name', $param['tw_name']);
        }
        if (!empty($param['tw_screen_name'])) {
            $twInformation->set('tw_screen_name', $param['tw_screen_name']);
        }
        if (!empty($param['tw_description'])) {
            $twInformation->set('tw_description', $param['tw_description']);
        }
        if (!empty($param['tw_url'])) {
            $twInformation->set('tw_url', $param['tw_url']);
        }
        if (!empty($param['tw_lang'])) {
            $twInformation->set('tw_lang', $param['tw_lang']);
        }
        if (!empty($param['tw_profile_image_url'])) {
            $twInformation->set('tw_profile_image_url', $param['tw_profile_image_url']);
        }
        if (!empty($param['tw_profile_image_url_https'])) {
            $twInformation->set('tw_profile_image_url_https', $param['tw_profile_image_url_https']);
        }
        // AnhMH 2016/06/21 Save email
        if (!empty($param['email'])) {
            $twInformation->set('tw_email', $param['email']);
        }
        // End
        if ($twInformation->save()) {
            if (empty($twInformation->id)) {
                $twInformation->id = self::cached_object($twInformation)->_original['id'];
            }
            return !empty($twInformation->id) ? $twInformation->id : 0;
        }
        return false;
    }
}
