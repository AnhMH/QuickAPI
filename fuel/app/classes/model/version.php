<?php

use Fuel\Core\DB;

/**
 * Any query in Model Version
 *
 * @package Model
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Model_Version extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'os',// android or ios
        'version',
        'version_code',
        'force_update',
        'announce_start_date',
        'created',
        'updated',
        'disable'
    );

    protected static $_observers = array(
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
    protected static $_table_name = 'versions';

    /**
     * 
     * @param type $param
     * @return boolean
     */
    public static function checkNew($param) {
        // Check valid param
        if (empty($param['os']) || !in_array(strtolower($param['os']), Config::get('os'))) {
            self::errorNotExist('os');
            return false;
        }
        $currentVersionCode = \Lib\Util::getVersionCode();
        if (empty($currentVersionCode) || !is_numeric($currentVersionCode)) {
            self::errorNotExist('current_version_code');
            return false;
        }
        
        // Build query
        $query = DB::select()->from(self::$_table_name);
        $query->where('os', $param['os']);
        $query->where('disable', 0);
        $query->where('version_code', '>', $currentVersionCode);
        $query->where(
            DB::expr('(announce_start_date = 0 OR announce_start_date IS NULL OR FROM_UNIXTIME(announce_start_date) <= FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())))')
        );
        $query->order_by('version_code', 'DESC');
        
        // Get data
        $versions = $query->execute(self::$slave_db)->as_array();
        
        // Build response
        $app_store_url = '';
        if ($param['os'] == Config::get('os')['ios']) {
            $app_store_url = Config::get('app_store_url')['ios'];
        } else if ($param['os'] == Config::get('os')['android']) {
            $app_store_url = Config::get('app_store_url')['android'];
        }
        $result = array(
            'latest_version_code' => intval($currentVersionCode),
            'version_name'        => '',
            'force_update'        => 0,
            'app_store_url'       => $app_store_url
        );
        if (!empty($versions)) {
            $result['latest_version_code'] = intval($versions[0]['version_code']);
            $result['version_name']        = !empty($versions[0]['version']) ? $versions[0]['version'] : '';
            foreach ($versions as $version) {
                if (!empty($version['force_update'])) {
                    $result['force_update'] = 1;
                    break;
                }
            }
        }
        
        // Return
        return $result;
    }
    
}
