<?php

//ip_in_range() and decbin32() functions stolen from
//https://github.com/cloudflarearchive/Cloudflare-Tools/blob/master/cf-joomla/plgCloudFlare/ip_in_range.php

function decbin32 ($dec) {
    return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}

function ip_in_range($ip, $range) {
    if (strpos($range, '/') !== false) {
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
        } else {
            $x = explode('.', $range);
            while(count($x)<4) $x[] = '0';
            list($a,$b,$c,$d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);
            $wildcard_dec = pow(2, (32-$netmask)) - 1;
            $netmask_dec = ~ $wildcard_dec;

            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    } else {
        if (strpos($range, '*') !==false) {
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        if (strpos($range, '-')!==false) {
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u",ip2long($lower));
            $upper_dec = (float)sprintf("%u",ip2long($upper));
            $ip_dec = (float)sprintf("%u",ip2long($ip));
            return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
        }
        return false;
    }
}

function get_ip_adress() {
    $cloudflare_ip_ranges = array(
        '204.93.240.0/24',
        '204.93.177.0/24',
        '199.27.128.0/21',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15'
    );

    $ip_adress = "cloudflare";

    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])){

        $valid_cf_request = false;

        foreach($cloudflare_ip_ranges as $range){
            if(ip_in_range($_SERVER['REMOTE_ADDR'], $range)) {
                $valid_cf_request = true;
                break;
            }
        }

        if($valid_cf_request){
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        } else{
            return $_SERVER['REMOTE_ADDR'];
        }

    } else{
        return $_SERVER['REMOTE_ADDR'];
    }
}
