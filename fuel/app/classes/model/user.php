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
    public static function get_login($param)
    {
        \LogLib::info('Login', __METHOD__, $param);
        $login = array();
        $user = self::find('first', array(
            'where' => array(
                'email' => $param['email'],
                'password' => Lib\Util::encodePassword($param['password'], $param['email'])
            )
        ));
        
        if (!empty($user['id'])) {
            $login = self::get_profile(array(
                'user_id' => $user['id']
            ));
        }
        
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
     * Get profile
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool Detail User or false if error
     */
    public static function get_profile($param)
    {
        if (empty($param['user_id'])) {
            return false;
        }
        
        $query = DB::select(
                self::$_table_name.'.id',
                self::$_table_name.'.name',
                self::$_table_name.'.email',
                self::$_table_name.'.description',
                self::$_table_name.'.image_path',
                self::$_table_name.'.is_mail_check'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name . '.id', $param['user_id'])
        ;
        
        $data = $query->execute()->offsetGet(0);
        
        return $data;
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
        if (empty($param['password']) && $is_new) {
            $param['password'] = Lib\Str::generate_password();
        }
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
    
    /**
     * Login facebook by token.
     *
     * @author AnhMH
     * @param array $param Input data.
     * @return bool Returns the boolean.
     */
    public static function login_facebook_by_token($param)
    {
        @session_start();
        try {
            //\LogLib::info('test fblogim- Get token from cookie', __METHOD__, array(\Config::get('facebook.app_id'), \Config::get('facebook.app_secret')));
            FacebookSession::setDefaultApplication(\Config::get('facebook.app_id'), \Config::get('facebook.app_secret'));
            \LogLib::info('login_facebook_by_token - Get token from cookie', __METHOD__, $param);
            $session = new FacebookSession($param['token']);
            if (isset($session)) {
                \LogLib::info('login_facebook_by_token - Session is OK', __METHOD__, $param);
                $request = new FacebookRequest($session, 'GET', '/me');
                $response = $request->execute();
                $facebookInfo = (array)$response->getResponse();
                if (!empty($facebookInfo)) {
                    \LogLib::info('login_facebook_by_token - call login_facebook', __METHOD__, $facebookInfo);
                    $loginInfo = self::login_facebook($facebookInfo, $param);
                    $loginInfo['fb_token'] = $param['token'];
                    return $loginInfo;
                }
            } else {
                \LogLib::info('login_facebook_by_token - Session is not OK', __METHOD__, $param);
                return false;
            }
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
            \LogLib::warning($ex->getRawResponse(), __METHOD__, $param);
            static::errorOther(self::ERROR_CODE_OTHER_1, '', $ex->getRawResponse());
            return false;
        } catch (\Exception $ex) {
            // When validation fails or other local issues
            \LogLib::warning($ex->getMessage(), __METHOD__, $param);
            static::errorOther(self::ERROR_CODE_OTHER_2, '', $ex->getMessage());
            return false;
        }
        \LogLib::info('login_facebook_by_token - There is no token from cookie', __METHOD__, $param);
        return false;
    }
    
    /**
     * Login facebook
     *
     * @author AnhMH
     * @param array $facebookInfo Input data.
     * @return bool Returns the boolean.
     */
    public static function login_facebook($facebookInfo, $param = array())
    {
        if (empty($facebookInfo['email']) && empty($facebookInfo['id'])) {
            self::errorNotExist('facebook_id_and_email');
            return false;
        }
        $param['facebook_birthday'] = isset($facebookInfo['birthday']) ? $facebookInfo['birthday'] : '';
        $param['facebook_email'] = isset($facebookInfo['email']) ? $facebookInfo['email'] : '';
        $param['facebook_id'] = isset($facebookInfo['id']) ? $facebookInfo['id'] : '';
        $param['facebook_name'] = isset($facebookInfo['name']) ? $facebookInfo['name'] : '';
        $param['facebook_first_name'] = isset($facebookInfo['first_name']) ? $facebookInfo['first_name'] : '';
        $param['facebook_last_name'] = isset($facebookInfo['last_name']) ? $facebookInfo['last_name'] : '';
        $param['facebook_username'] = isset($facebookInfo['username']) ? $facebookInfo['username'] : '';
        $param['facebook_gender'] = isset($facebookInfo['gender']) ? $facebookInfo['gender'] : '';
        $param['facebook_link'] = isset($facebookInfo['link']) ? $facebookInfo['link'] : '';
        $param['facebook_image'] = "http://graph.facebook.com/{$param['facebook_id']}/picture?type=large";
        $param['os'] = isset($facebookInfo['os']) ? $facebookInfo['os'] : '';
        if (!empty($param['facebook_id'])) {
            $facebook = Model_User_Facebook_Information::get_detail(array(
                    'facebook_id' => $param['facebook_id'],
                    'disable'     => 0
                )
            );
        }
        if (!empty($facebook['facebook_id']) && $facebook['facebook_id'] != $param['facebook_id']) {
            if (Model_User_Facebook_Information::add_update(array(
                'id'          => $facebook['id'],
                'facebook_id' => $param['facebook_id'],
            ))
            ) {
                $facebook['facebook_id'] = $param['facebook_id'];
                \LogLib::info('Update facebook_id', __METHOD__, $param);
            }
        }

        $isNewUser = false; //use to differ first new login facebook or not
        if (!empty($facebook['user_id']) && !empty($facebook['facebook_id'])) {
            \LogLib::info('User used to login with facebook', __METHOD__, $facebook);
            $userId = $facebook['user_id'];
        } elseif (!empty($facebook['user_id']) && empty($facebook['facebook_id'])) {
            \LogLib::info('User used to login without facebook', __METHOD__, $facebook);
            $param['user_id'] = $facebook['user_id'];
            if (Model_User_Facebook_Information::add_update($param)) {
                \LogLib::info('Update facebook info', __METHOD__, $param);
                $userId = $facebook['user_id'];
            }
        } else {
            $isNewUser = true;
            \LogLib::info('First login using facebook', __METHOD__, $param);
            $param['email'] = $param['facebook_email'];
            // AnhMH 2016/06/21 Check dulicate email.
            if (!empty($param['email'])) {
                $option['where'] = array(
                    'email' => $param['email']
                );
                $checkEmailDulicate = self::find('first', $option);
                if (!empty($checkEmailDulicate)) {
                    \LogLib::info('Duplicate email in users', __METHOD__, $param);
                    self::errorDuplicate('email', $param['email']);
                    return false;
                }
            }
            // End.
            $param['password'] = '';
            $param['name'] = $param['facebook_name'];
            $param['image_path'] = $param['facebook_image'];
            $userId = self::add_update($param);
            if ($userId > 0) {
                // Add user_facebook_information
                $param['user_id'] = $userId;
                if (Model_User_Facebook_Information::add_update($param)) {
                    \LogLib::info('Add facebook info', __METHOD__, $param);
                }
            }
        }
        if (!empty($userId)) {
            \LogLib::info('Return user info', __METHOD__, $param);
            $data = self::get_profile(array('user_id' => $userId));  
            return $data;
        }
        \LogLib::info('User info unavailable', __METHOD__, $param);
        self::errorNotExist('fb_user_information');
        return false;
    }
    
    /**
     * Login Twitter by token.
     *
     * @author caolp
     * @param array $param Input data.
     * @return bool Returns the boolean.
     */
    public static function login_twitter_by_token($param)
    {
        @session_start();
        try {
            $twitter = \Social\Twitter::forge($param['oauth_token'], $param['oauth_token_secret']);
            \LogLib::info('login_twitter_by_token - Get info', __METHOD__, $param);
            if ($twitter) {
                \LogLib::info('login_twitter_by_token - Session is OK', __METHOD__);
                $twitterInfo = $twitter->get('account/verify_credentials', array(
                    'include_email' => 'true' // Get email
                ));
                if (!empty($twitterInfo)) {
                    \LogLib::info('login_twitter_by_token - call login_twitter', __METHOD__, $twitterInfo);
                    $loginInfo = self::login_twitter($twitterInfo, $param);
                    $loginInfo['oauth_token'] = $param['oauth_token'];
                    $loginInfo['oauth_token_secret'] = $param['oauth_token_secret'];
                    return $loginInfo;
                }
            } else {
                \LogLib::info('login_twitter_by_token - Session is not OK', __METHOD__, $param);
                return false;
            }
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
            \LogLib::warning($ex->getRawResponse(), __METHOD__, $param);
            static::errorOther(self::ERROR_CODE_OTHER_1, '', $ex->getRawResponse());
            return false;
        } catch (\Exception $ex) {
            // When validation fails or other local issues
            \LogLib::warning($ex->getMessage(), __METHOD__, $param);
            static::errorOther(self::ERROR_CODE_OTHER_2, '', $ex->getMessage());
            return false;
        }
        \LogLib::info('login_twitter_by_token - There is no token from cookie', __METHOD__, $param);
        return false;
    }
    
    /**
     * Login Twitter
     *
     * @author AnhMH
     * @param array $twitterInfo Input data.
     * @return bool Returns the boolean.
     */
    public static function login_twitter($twitterInfo, $param = array())
    {
        if (empty($twitterInfo->id)) {
            self::errorNotExist('twitter_id');
            return false;
        }
        $param['tw_id'] = isset($twitterInfo->id) ? $twitterInfo->id : '';
        $param['tw_name'] = isset($twitterInfo->name) ? $twitterInfo->name : '';
        $param['tw_screen_name'] = isset($twitterInfo->screen_name) ? $twitterInfo->screen_name : '';
        $param['tw_description'] = isset($twitterInfo->description) ? $twitterInfo->description : '';
        $param['tw_url'] = isset($twitterInfo->url) ? $twitterInfo->url : '';
        $param['tw_lang'] = isset($twitterInfo->lang) ? $twitterInfo->lang : '';
        $param['tw_profile_image_url'] = isset($twitterInfo->profile_image_url) ? $twitterInfo->profile_image_url : '';
        $param['tw_profile_image_url_https'] = isset($twitterInfo->profile_image_url_https) ? $twitterInfo->profile_image_url_https : '';

        if (!empty($param['tw_id'])) {
            $twitter = Model_User_Twitter_Information::get_detail(array(
                    'tw_id'   => $param['tw_id'],
                    'disable' => 0
                )
            );
        }
        $isNewUser = false; //use to differ first new login twitter or not
        if (!empty($twitter['user_id']) && !empty($twitter['tw_id'])) {
            $userId = $twitter['user_id'];
        } elseif (!empty($twitter['user_id']) && empty($twitter['tw_id'])) {
            $param['user_id'] = $twitter['user_id'];
            if (Model_User_Twitter_Information::add_update($param)) {
                $userId = $twitter['user_id'];
            }
        } else {
            $isNewUser = true;
            $param['email'] = isset($twitterInfo->email) ? $twitterInfo->email : '';
            // AnhMH 2016/06/21 Check dulicate email.
            if (!empty($param['email'])) {
                $option['where'] = array(
                    'email' => $param['email']
                );
                $checkEmailDulicate = self::find('first', $option);
                if (!empty($checkEmailDulicate)) {
                    \LogLib::info('Duplicate email in users', __METHOD__, $param);
                    self::errorDuplicate('email', $param['email']);
                    return false;
                }
            }
            // End.
            $param['password'] = '';
            $param['name'] = $param['tw_name'];
            $param['image_path'] = $param['tw_profile_image_url'];
            $userId = Model_User::add_update($param);
            if ($userId > 0) {
                // Add user_twitter_information
                $param['user_id'] = $userId;
                if (Model_User_Twitter_Information::add_update($param)) {
                    \LogLib::info('Add twitter info', __METHOD__);
                }
            }
        }
        if (!empty($userId)) {
            \LogLib::info('Return user info', __METHOD__);
            $data = self::get_profile(array(
                'user_id' => $userId
            ));  
            if (!empty($data)) {
                $data['is_new_user'] = $isNewUser;
                return $data;
            }
        }
        \LogLib::info('User info unavailable', __METHOD__, $param);
        self::errorNotExist('tw_user_information');
        return false;
    }
    
}
