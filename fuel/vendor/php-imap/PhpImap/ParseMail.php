<?php

namespace PhpImap;

use Fuel\Core\Cli;
use LogLib\LogLib;

/**
 * @author caolp
 */
class ParseMail {

    public function parse_mail($mail, $mailId) {
        preg_match_all('/(■.+\n.+)/', $mail, $re);
        $result = array();
        foreach ($re[0] as $item) {
            $string = trim(preg_replace('/(\n　|\n)/', '!', $item));
            $t = @split('!', $string);
            if (count($t) > 0) {
                $result[$t[0]] = trim($t[1]);
            }
        }
        preg_match('/ご予約.+/', $mail, $type);
        if (isset($type[0])) {
            if (trim($type[0]) == "ご予約のキャンセルがありました。")
                $result['is_cancel'] = 1;
            else
                $result['is_cancel'] = 0;
        }
        preg_match('/FAST NAIL (.+)様/', $mail, $shop_name);
        if (isset($shop_name[1])) {
            $result['shop_name'] = trim($shop_name[1]);
        }
        preg_match('/予約受付日時：(.+)/', $mail, $created);
        if (isset($created[1])) {
            $result['created'] = $created[1];
        }
        return $result;
    }

    public function parseDate($date) {
        $date = trim(preg_replace('/（.+）/', ' ', $date));
        $date = date_create_from_format('Y年m月d日 H:i', $date);
        return $date->format('Y-m-d H:i');
    }

    public function parseMoney($money) {
        $money = str_replace(',', '', $money);
        preg_match('/\d+円/', $money, $re);
        if (isset($re[0]))
            return str_replace('円', '', $re[0]);
        else
            return 0;
    }

    public function parseName($name) {
        preg_match('/(.+)（(.+)）/', $name, $parseName);
        return $parseName;
    }

}
