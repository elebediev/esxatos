<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'is_mobile',
        'country',
        'city',
        'logged_in_at',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'logged_in_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse user agent string to extract device info.
     */
    public static function parseUserAgent(string $userAgent): array
    {
        $result = [
            'device_type' => 'desktop',
            'browser' => null,
            'browser_version' => null,
            'platform' => null,
            'platform_version' => null,
            'is_mobile' => false,
        ];

        // Detect mobile/tablet
        $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'webOS', 'BlackBerry', 'Opera Mini', 'IEMobile'];
        $tabletKeywords = ['iPad', 'Tablet', 'PlayBook', 'Silk'];

        foreach ($tabletKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                $result['device_type'] = 'tablet';
                $result['is_mobile'] = true;
                break;
            }
        }

        if ($result['device_type'] === 'desktop') {
            foreach ($mobileKeywords as $keyword) {
                if (stripos($userAgent, $keyword) !== false) {
                    $result['device_type'] = 'mobile';
                    $result['is_mobile'] = true;
                    break;
                }
            }
        }

        // Detect platform
        $platforms = [
            'Windows NT 10' => ['Windows', '10'],
            'Windows NT 6.3' => ['Windows', '8.1'],
            'Windows NT 6.2' => ['Windows', '8'],
            'Windows NT 6.1' => ['Windows', '7'],
            'Mac OS X' => ['macOS', null],
            'iPhone OS' => ['iOS', null],
            'iPad' => ['iPadOS', null],
            'Android' => ['Android', null],
            'Linux' => ['Linux', null],
            'Ubuntu' => ['Ubuntu', null],
            'CrOS' => ['Chrome OS', null],
        ];

        foreach ($platforms as $pattern => $info) {
            if (stripos($userAgent, $pattern) !== false) {
                $result['platform'] = $info[0];
                if ($info[1]) {
                    $result['platform_version'] = $info[1];
                } else {
                    // Try to extract version
                    if (preg_match('/' . preg_quote($pattern, '/') . '[\/\s]*([\d._]+)/i', $userAgent, $matches)) {
                        $result['platform_version'] = str_replace('_', '.', $matches[1]);
                    }
                }
                break;
            }
        }

        // Detect browser
        $browsers = [
            'Edg/' => 'Edge',
            'OPR/' => 'Opera',
            'Opera/' => 'Opera',
            'YaBrowser/' => 'Yandex Browser',
            'Chrome/' => 'Chrome',
            'Firefox/' => 'Firefox',
            'Safari/' => 'Safari',
            'MSIE' => 'Internet Explorer',
            'Trident/' => 'Internet Explorer',
        ];

        foreach ($browsers as $pattern => $browserName) {
            if (stripos($userAgent, $pattern) !== false) {
                $result['browser'] = $browserName;
                // Extract version
                if (preg_match('/' . preg_quote($pattern, '/') . '([\d.]+)/i', $userAgent, $matches)) {
                    $result['browser_version'] = $matches[1];
                }
                break;
            }
        }

        // Safari detection fix (comes after Chrome in UA string)
        if ($result['browser'] === 'Safari' && stripos($userAgent, 'Chrome') !== false) {
            $result['browser'] = 'Chrome';
            if (preg_match('/Chrome\/([\d.]+)/i', $userAgent, $matches)) {
                $result['browser_version'] = $matches[1];
            }
        }

        return $result;
    }

    /**
     * Log a new login event.
     */
    public static function logLogin(int $userId, string $ipAddress, ?string $userAgent): self
    {
        $deviceInfo = $userAgent ? self::parseUserAgent($userAgent) : [];

        return self::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceInfo['device_type'] ?? 'desktop',
            'browser' => $deviceInfo['browser'] ?? null,
            'browser_version' => $deviceInfo['browser_version'] ?? null,
            'platform' => $deviceInfo['platform'] ?? null,
            'platform_version' => $deviceInfo['platform_version'] ?? null,
            'is_mobile' => $deviceInfo['is_mobile'] ?? false,
            'logged_in_at' => now(),
        ]);
    }
}
