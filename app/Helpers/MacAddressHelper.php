<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class MacAddressHelper
{
    /**
     * Get MAC address from IP address (local network only)
     */
    public static function getMacAddress($ipAddress)
    {
        // Validate IP address format
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            Log::warning('Invalid IP address provided', ['ip' => $ipAddress]);
            return null;
        }
        
        // Skip private IP ranges that might not be accessible
        if (self::isPrivateIp($ipAddress)) {
            return null;
        }
        
        try {
            // For Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $arp = `arp -a {$ipAddress} 2>nul`;
                if (preg_match('/[0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}[-][0-9a-f]{2}/i', $arp, $matches)) {
                    return strtoupper($matches[0]);
                }
            } 
            // For Linux/Mac
            else {
                // Try multiple ARP commands
                $commands = [
                    "arp -n {$ipAddress} 2>/dev/null",
                    "arp -a {$ipAddress} 2>/dev/null",
                    "ip neigh show {$ipAddress} 2>/dev/null"
                ];
                
                foreach ($commands as $command) {
                    $arp = `$command`;
                    if (preg_match('/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i', $arp, $matches)) {
                        return strtoupper($matches[1]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting MAC address', [
                'ip' => $ipAddress,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * Get client MAC address from request
     */
    public static function getClientMac($request)
    {
        $ipAddress = $request->ip();
        
        // Handle localhost
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return 'LOCALHOST';
        }
        
        // Get MAC address
        $mac = self::getMacAddress($ipAddress);
        
        // Log when MAC cannot be retrieved (for debugging)
        if (!$mac && !self::isPrivateIp($ipAddress)) {
            Log::info('MAC address not found in ARP table', [
                'ip' => $ipAddress,
                'server_os' => PHP_OS,
                'environment' => app()->environment()
            ]);
        }
        
        return $mac;
    }
    
    /**
     * Check if IP is private/internal
     */
    private static function isPrivateIp($ipAddress)
    {
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8'
        ];
        
        foreach ($privateRanges as $range) {
            if (self::ipInRange($ipAddress, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP is within CIDR range
     */
    private static function ipInRange($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);
        $subnetLong &= $maskLong;
        
        return ($ipLong & $maskLong) == $subnetLong;
    }
    
    /**
     * Check if we're in a cloud environment
     */
    public static function isCloudEnvironment()
    {
        // Check for common cloud environment indicators
        $cloudIndicators = [
            'AWS_EXECUTION_ENV' => getenv('AWS_EXECUTION_ENV'),
            'KUBERNETES_SERVICE_HOST' => getenv('KUBERNETES_SERVICE_HOST'),
            'DYNO' => getenv('DYNO'), // Heroku
            'WEBSITE_HOSTNAME' => getenv('WEBSITE_HOSTNAME'), // Azure
        ];
        
        foreach ($cloudIndicators as $indicator => $value) {
            if ($value) {
                return true;
            }
        }
        
        // Check for cloud IP ranges (simplified)
        $serverIp = $_SERVER['SERVER_ADDR'] ?? '';
        if (strpos($serverIp, '100.64.0.0') === 0) { // CGNAT range
            return true;
        }
        
        return false;
    }
    
    /**
     * Get MAC with environment-aware handling
     */
    public static function getClientMacSmart($request)
    {
        // If in cloud environment, MAC logging is not possible
        if (self::isCloudEnvironment()) {
            Log::notice('MAC address logging skipped - cloud environment detected');
            return 'CLOUD_ENVIRONMENT';
        }
        
        $mac = self::getClientMac($request);
        
        // Return meaningful message for debugging
        if (!$mac) {
            return 'MAC_NOT_FOUND';
        }
        
        return $mac;
    }
}