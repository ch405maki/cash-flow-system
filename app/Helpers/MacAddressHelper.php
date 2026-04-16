<?php

namespace App\Helpers;

class MacAddressHelper
{
    /**
     * Get MAC address from IP address (local network only)
     */
    public static function getMacAddress($ipAddress)
    {
        // For Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $arp = `arp -a {$ipAddress}`;
            if (preg_match('/[0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}/i', $arp, $matches)) {
                return strtoupper($matches[0]);
            }
        } 
        // For Linux/Mac
        else {
            $arp = `arp -n {$ipAddress}`;
            if (preg_match('/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i', $arp, $matches)) {
                return strtoupper($matches[1]);
            }
        }
        
        return null;
    }
    
    /**
     * Get client MAC address from request
     */
    public static function getClientMac($request)
    {
        $ipAddress = $request->ip();
        
        // Skip localhost/private IPs that might not be in ARP table
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return 'LOCALHOST';
        }
        
        return self::getMacAddress($ipAddress);
    }
}