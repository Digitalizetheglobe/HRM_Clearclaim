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
        $allowedParts = explode('.', trim($this->ip));
        $clientParts = explode('.', trim($clientIp));
        
        // Ensure there are parts to compare
        if (count($allowedParts) === 0 || count($clientParts) === 0) {
            return false;
        }
        
        // Loop through each octet defined in the allowed IP
        foreach ($allowedParts as $index => $part) {
            // If the client IP doesn't have this octet, or it doesn't match, return false
            if (!isset($clientParts[$index]) || $clientParts[$index] !== $part) {
                return false;
            }
        }
        
        return true;
    }
}
