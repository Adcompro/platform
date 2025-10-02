<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class DateHelper
{
    /**
     * Convert setting format to PHP date format
     */
    private static function convertDateFormat($settingFormat)
    {
        $formats = [
            'DD-MM-YYYY' => 'd-m-Y',
            'MM-DD-YYYY' => 'm-d-Y',
            'YYYY-MM-DD' => 'Y-m-d',
            'DD/MM/YYYY' => 'd/m/Y',
            'MM/DD/YYYY' => 'm/d/Y',
            'YYYY/MM/DD' => 'Y/m/d',
        ];

        return $formats[$settingFormat] ?? 'd-m-Y';
    }

    /**
     * Convert setting time format to PHP time format
     */
    private static function convertTimeFormat($settingFormat)
    {
        $formats = [
            '24-hour' => 'H:i',
            '12-hour' => 'g:i A',
        ];

        return $formats[$settingFormat] ?? 'H:i';
    }

    /**
     * Format a date/time with the configured timezone and format
     */
    public static function format($date, $includeTime = true)
    {
        if (!$date) {
            return '';
        }

        // Get settings
        $timezone = Setting::get('app_timezone', 'Europe/Amsterdam');
        $dateFormatSetting = Setting::get('app_date_format', 'DD-MM-YYYY');
        $timeFormatSetting = Setting::get('app_time_format', '24-hour');

        // Convert to PHP formats
        $dateFormat = self::convertDateFormat($dateFormatSetting);
        $timeFormat = self::convertTimeFormat($timeFormatSetting);

        // Convert to Carbon instance if needed
        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        // Set timezone
        $date->setTimezone($timezone);

        // Return formatted string
        if ($includeTime) {
            return $date->format($dateFormat . ' ' . $timeFormat);
        } else {
            return $date->format($dateFormat);
        }
    }

    /**
     * Format date only
     */
    public static function formatDate($date)
    {
        return self::format($date, false);
    }

    /**
     * Format time only
     */
    public static function formatTime($date)
    {
        if (!$date) {
            return '';
        }

        $timezone = Setting::get('app_timezone', 'Europe/Amsterdam');
        $timeFormatSetting = Setting::get('app_time_format', '24-hour');
        $timeFormat = self::convertTimeFormat($timeFormatSetting);

        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        return $date->setTimezone($timezone)->format($timeFormat);
    }

    /**
     * Get current datetime in configured timezone
     */
    public static function now()
    {
        $timezone = Setting::get('app_timezone', 'Europe/Amsterdam');
        return Carbon::now($timezone);
    }

    /**
     * Format date parts for compact display (like in calendar event cards)
     */
    public static function formatDateParts($date)
    {
        if (!$date) {
            return ['month' => '', 'day' => '', 'time' => ''];
        }

        $timezone = Setting::get('app_timezone', 'Europe/Amsterdam');
        $dateFormatSetting = Setting::get('app_date_format', 'DD-MM-YYYY');

        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        $date->setTimezone($timezone);

        // Determine month/day order based on date format
        $monthFirst = str_starts_with($dateFormatSetting, 'MM');

        return [
            'month' => $date->format('M'),
            'day' => $date->format('d'),
            'time' => self::formatTime($date),
            'month_first' => $monthFirst
        ];
    }
}