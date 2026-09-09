<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

final class DateRangeResolver
{
    public function __construct(private string $timezone = 'Africa/Dar_es_Salaam') {}

    public function resolve(string $text): array
    {
        $tz = new DateTimeZone($this->timezone);
        $today = new DateTimeImmutable('today', $tz);
        $q = mb_strtolower(trim($text));
        $start = $today;
        $end = $today;
        $label = 'today';
        $matched = true;

        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $q, $m)) {
            $start = new DateTimeImmutable($m[0], $tz); $end = $start; $label = $m[0];
        } elseif (str_contains($q, 'yesterday')) {
            $start = $today->modify('-1 day'); $end = $start; $label = 'yesterday';
        } elseif (str_contains($q, 'last sunday')) {
            $start = strtolower($today->format('l')) === 'sunday' ? $today->modify('-7 days') : $today->modify('last sunday');
            $end = $start; $label = 'last Sunday';
        } elseif (str_contains($q, 'this week')) {
            $start = $today->modify('monday this week'); $end = $today; $label = 'this week';
        } elseif (str_contains($q, 'last week')) {
            $start = $today->modify('monday last week'); $end = $start->modify('+6 days'); $label = 'last week';
        } elseif (str_contains($q, 'last month')) {
            $start = $today->modify('first day of last month'); $end = $today->modify('last day of last month'); $label = 'last month';
        } elseif (str_contains($q, 'this month') || str_contains($q, 'current month')) {
            $start = $today->modify('first day of this month'); $end = $today->modify('last day of this month'); $label = 'this month';
        } elseif (str_contains($q, 'today')) {
            $label = 'today';
        } else {
            $matched = false;
            $start = $today->modify('first day of this month'); $end = $today->modify('last day of this month'); $label = 'this month (default)';
        }

        return [
            'matched' => $matched,
            'label' => $label,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ];
    }
}
