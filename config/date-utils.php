<?php

function getDateAsDateTime(string $date): DateTime 
{
    return new DateTime($date);
}

function isDateOnWeekend(string $date): bool 
{
    return in_array(getDateAsDateTime($date)->format('N'), [6, 7]);
}

function isDateBefore(string $date1, string $date2): bool  
{
    return getDateAsDateTime($date1)->getTimestamp() < getDateAsDateTime($date2)->getTimestamp();
}

function getNextDay(string $date): DateTime 
{
    $inputDate = getDateAsDateTime($date);
    $inputDate->modify('+1 day');
    return $inputDate;
}

function sumIntervals(array $intervals): DateInterval
{
    $date = new DateTime('00:00:00');
    foreach($intervals as $interval) {
        $date->add($interval);
    }
    
    return (new DateTime('00:00:00'))->diff($date);
}

function getDateFromInterval(string $pattern, DateInterval $interval): DateTimeImmutable
{
    return new DateTimeImmutable($interval->format($pattern));
}

function getDateFromString(string $pattern, string $str): DateTimeImmutable|false
{
    return DateTimeImmutable::createFromFormat($pattern, $str);
}

function getFirstDayOfMonth(string $date): DateTime
{
    return new DateTime(date('Y-m-1', getDateAsDateTime($date)->getTimestamp()));
}

function getLastDayOfMonth(string $date): DateTime
{
    return new DateTime(date('Y-m-t', getDateAsDateTime($date)->getTimestamp()));
}

function getNextWeekday(string $date, array $weekdays = []): string
{
    $weekdays = count($weekdays) > 0 ? $weekdays : [1, 2, 3, 4, 5, 6, 7];
    $dt = (new DateTime($date));
    $dt->modify('+1 day');

    while(!in_array($dt->format('N'), $weekdays)) {
        $dt->modify('+1 day');
    }

    return $dt->format('Y-m-d');
}

function getSecondsFromDateInterval(DateInterval $interval): int 
{
    $d1 = new DateTimeImmutable();
    $d2 = $d1->add($interval);
    return $d2->getTimestamp() - $d1->getTimestamp();
}

function getTimeStringFromSeconds(int $seconds): string
{
    return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds - ($h * 3600) - ($m * 60));
}

function isDateAfter(string $date1, string $date2): bool
{
    return getDateAsDateTime($date1)->getTimestamp() > getDateAsDateTime($date2)->getTimestamp();
}