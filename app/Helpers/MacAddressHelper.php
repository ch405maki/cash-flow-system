<?php

namespace App\Helpers;

class MacAddressHelper
{
    /**
     * Get MAC address from IP address (local network only)
     */
    public static function getMacAddress(string $ipAddress): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Ping first to ensure the IP is in the ARP table
        if ($isWindows) {
            shell_exec("ping -n 1 -w 500 {$ipAddress}");
        } else {
            shell_exec("/usr/bin/ping -c 1 -W 1 {$ipAddress}");
        }

        return $isWindows
            ? self::getMacWindows($ipAddress)
            : self::getMacLinux($ipAddress);
    }

    private static function getMacWindows(string $ip): ?string
    {
        $output = shell_exec("arp -a {$ip}");

        if ($output && preg_match(
            '/[0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}/i',
            $output,
            $matches
        )) {
            return strtoupper(str_replace('-', ':', $matches[0]));
        }

        return null;
    }

    private static function getMacLinux(string $ip): ?string
    {
        // 1. Try `ip neigh` first (modern Linux)
        $output = shell_exec("/usr/sbin/ip neigh show {$ip} 2>/dev/null");

        if ($output && !str_contains(strtoupper($output), 'FAILED')) {
            if (preg_match(
                '/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i',
                $output,
                $matches
            )) {
                return strtoupper($matches[1]);
            }
        }

        // 2. Fallback to `arp -n` with full path
        $output = shell_exec("/usr/sbin/arp -n {$ip} 2>/dev/null");

        if ($output && preg_match(
            '/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i',
            $output,
            $matches
        )) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    /**
     * Get client MAC address from request
     */
    public static function getClientMac($request): string
    {
        $ipAddress = $request->ip();

        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return 'LOCALHOST';
        }

        return self::getMacAddress($ipAddress) ?? "MAC_UNRESOLVABLE:{$ipAddress}";
    }
}