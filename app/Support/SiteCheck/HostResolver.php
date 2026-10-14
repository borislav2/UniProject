<?php

namespace App\Support\SiteCheck;

/**
 * Намира IP адреса на домейн за проверката на сайт и отказва адреси във вътрешни мрежи,
 * за да не може формата да се използва за достъп до нашия сървър или локалната мрежа.
 */
class HostResolver
{
    public function publicIp(string $host): ?string
    {
        $ips = gethostbynamel($host) ?: [];
        $flags = defined('FILTER_FLAG_GLOBAL_RANGE') ? FILTER_FLAG_GLOBAL_RANGE : FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | $flags)) {
                return null;
            }
        }

        return $ips[0] ?? null;
    }
}
