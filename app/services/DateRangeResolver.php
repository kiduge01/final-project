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
        } elseif (preg_match('/\b(?:last|past|previous)\s+(\d{1,2})\s*(days?|weeks?|months?|years?)\b/u', $q, $m)
            || preg_match('/\b(\d{1,2})\s*(days?|weeks?|months?|years?)\b/u', $q, $m)) {
            $count = max(1, min(60, (int)$m[1]));
            $unit = rtrim($m[2], 's');
            if ($unit === 'day') {
                $start = $today->modify('-'.($count - 1).' days');
                $end = $today;
            } elseif ($unit === 'week') {
                $start = $today->modify('monday this week')->modify('-'.($count - 1).' weeks');
                $end = $today->modify('sunday this week');
            } elseif ($unit === 'month') {
                $start = $today->modify('first day of this month')->modify('-'.($count - 1).' months');
                $end = $today->modify('last day of this month');
            } else {
                $start = $today->setDate((int)$today->format('Y'), 1, 1)->modify('-'.($count - 1).' years');
                $end = $today->setDate((int)$today->format('Y'), 12, 31);
            }
            $label = $count.' '.$unit.($count === 1 ? '' : 's');
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
        } elseif (str_contains($q, 'this quarter') || str_contains($q, 'current quarter')) {
            $quarterStartMonth = intdiv(((int)$today->format('n')) - 1, 3) * 3 + 1;
            $start = $today->setDate((int)$today->format('Y'), $quarterStartMonth, 1);
            $end = $start->modify('+3 months -1 day');
            $label = 'this quarter';
        } elseif (str_contains($q, 'last quarter')) {
            $quarterStartMonth = intdiv(((int)$today->format('n')) - 1, 3) * 3 + 1;
            $start = $today->setDate((int)$today->format('Y'), $quarterStartMonth, 1)->modify('-3 months');
            $end = $start->modify('+3 months -1 day');
            $label = 'last quarter';
        } elseif (str_contains($q, 'this year') || str_contains($q, 'current year')) {
            $start = $today->setDate((int)$today->format('Y'), 1, 1);
            $end = $today->setDate((int)$today->format('Y'), 12, 31);
            $label = 'this year';
        } elseif (str_contains($q, 'last year')) {
            $year = ((int)$today->format('Y')) - 1;
            $start = $today->setDate($year, 1, 1);
            $end = $today->setDate($year, 12, 31);
            $label = 'last year';
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
