<?php namespace MotionArray\Helpers;

use Carbon\Carbon;
use DateTime;

class Helpers
{
    static $bytesInGB = 1073741824;
    static $kbInGB = 1048576;

    public static function bytesToGB($bytes)
    {
        $gb = $bytes / self::$bytesInGB;

        $gbFormatted = floor($gb * 100) / 100;

        return $gbFormatted;
    }

    public static function bytesToKb($bytes)
    {
        return floor($bytes / 1000);
    }

    /**
     * Bytes to size converter
     */
    public static function bytesToSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function gbToBytes($gb)
    {
        return $gb * self::$bytesInGB;
    }

    public static function gbToKb($gb)
    {
        return $gb * self::$kbInGB;
    }

    public static function kbToGb($kb)
    {
        return $kb / self::$kbInGB;
    }

    public static function timeElapsedString(DateTime $datetime, $full = false)
    {
        $now = Carbon::now();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if ($full) {
            $string = array_slice($string, 0, 2);
        } else {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(' and ', $string) . ' ago' : 'just now';
    }

    public static function convertToHttps($url): string
    {
        return preg_replace('/^http:/i', 'https:', $url);
    }
}
