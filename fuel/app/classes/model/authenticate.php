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
        \LogLib::debug('user_id', __METHOD__, \Lib\Util::authUserId());
        \LogLib::debug('token', __METHOD__, \Lib\Util::authToken());
        $param = array(
            'user_id' => \Lib\Util::authUserId(),
            'token' => \Lib\Util::authToken(),
        );
        $query = DB::select(
                        'id', 'user_id', 'token', 'expire_date', 'regist_type', 'created', DB::expr("UNIX_TIMESTAMP() AS systime")
                )
                ->from(self::$_table_name)
                ->where('user_id', '=', $param['user_id'])
                ->where('token', '=', $param['token'])
                ->limit(1);

        \LogLib::debug('query end', __METHOD__, $query);
        
        $data = $query->execute()->as_array();
        $data = !empty($data[0]) ? $data[0] : array();

        \LogLib::debug('query execute end', __METHOD__, $data);
        if (empty($data)) {
            self::errorNotExist('token');
            \LogLib::warning('Token does not exist', __METHOD__, $param);
            return false;
        }
        if ($data['expire_date'] < $data['systime']) {
            \LogLib::warning('Token have been already expired', __METHOD__, $param);
            self::errorOther(self::ERROR_CODE_AUTH_ERROR, 'token');
            return false;
        }

        \LogLib::debug('method end', __METHOD__, $data);
        return $data;
    }

    /**
     * Addupdate info for authenticates.
     *
     * @author diennvt
     * @param array $param Input data.
     * @return string Returns the string of token.
     */
    public static function addupdate($param) {
        if (empty($param['user_id']) || empty($param['regist_type'])) {
            self::errorParamInvalid('user_id_or_regist_type');
            return false;
        }
        $token = '';
        $query = DB::select(
                    'id', 
                    'user_id', 
                    'token', 
                    'expire_date', 
                    'regist_type', 
                    'created', 
                    DB::expr("UNIX_TIMESTAMP() AS systime")
                )
                ->from(self::$_table_name)
                ->where('user_id', '=', $param['user_id'])
                ->where('regist_type', '=', $param['regist_type'])
                ->limit(1);
        $authenticate = $query->execute()->offsetGet(0);             
        if (empty($authenticate['id'])) {
            \LogLib::info('Create new token', __METHOD__, $param);
            $token = \Lib\Str::generate_token_for_api();
            $auth = new self;
            $auth->set('user_id', $param['user_id']);
            $auth->set('regist_type', $param['regist_type']);
            $auth->set('token', $token);
            $auth->set('expire_date', \Config::get('api_token_expire'));
            if (!$auth->create()) {
                \LogLib::warning('Can not create token', __METHOD__, $param);
            }
        } else {
            $auth = new self($authenticate, false);
            $auth->set('expire_date', \Config::get('api_token_expire'));
            $token = $authenticate['token'];
            if ($authenticate['expire_date'] < $authenticate['systime']) {
                \LogLib::info('Update new token', __METHOD__, $param);
                $token = \Lib\Str::generate_token_for_api();
                $auth->set('token', $token);
            }
            if (!$auth->update()) {
                \LogLib::warning('Can not update token', __METHOD__, $param);
            }
        }
        return $token;
    }
}
