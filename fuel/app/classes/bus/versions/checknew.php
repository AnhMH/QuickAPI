<?php

namespace Bus;

/**
 * Check version code
 *
 * @package Bus
 * @created 2017-07-03
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Versions_CheckNew extends BusAbstract {

    public function operateDB($data) {
        try {
            $this->_response = \Model_Version::checkNew($data);
            return $this->result(\Model_Version::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }

}
