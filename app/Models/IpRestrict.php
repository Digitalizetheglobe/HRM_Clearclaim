<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpRestrict extends Model
{
    protected $fillable = [
        'ip',
        'created_by',
    ];

    /**
     * Check if given IP matches this restriction (first three octets)
     */
    public function matchesIp($clientIp)
    {
        // Get first three octets of both IPs
        $allowedParts = explode('.', $this->ip);
        $clientParts = explode('.', $clientIp);
        
        if (count($allowedParts) >= 3 && count($clientParts) >= 3) {
            $allowedSubnet = $allowedParts[0] . '.' . $allowedParts[1] . '.' . $allowedParts[2];
            $clientSubnet = $clientParts[0] . '.' . $clientParts[1] . '.' . $clientParts[2];
            
            return $allowedSubnet === $clientSubnet;
        }
        
        return false;
    }
}
