<?php

class CallBackTimeValidator
{
    private const TIMETABLE = [
      1 => ['From' => 'PT9H', 'To' => 'PT17H'],
      2 => ['From' => 'PT9H', 'To' => 'PT17H'],
      3 => ['From' => 'PT9H', 'To' => 'PT12H'],
      4 => ['From' => 'PT9H', 'To' => 'PT17H'],
      5 => ['From' => 'PT9H', 'To' => 'PT17H'],
    ];

    public static function Validate(\DateTimeImmutable $dtProposed, \DateTimeImmutable $dtNow)
    {
        $openingHours = self::TIMETABLE[$dtProposed->format('N')] ?? null;
        if ($openingHours === null) {
            return false;
        }
        $openingProposedDay = clone($dtProposed)->setTime(0,0,0)->add(new DateInterval($openingHours['From']));
        if ($dtProposed < $openingProposedDay) {
            return false;
        }
        $closingProposedDay = clone($dtProposed)->setTime(0,0,0)->add(new DateInterval($openingHours['To']));
        if ($dtProposed > $closingProposedDay) {
            return false;
        }

        if ($dtProposed < $dtNow->add(new DateInterval("PT2H"))) {
            return false;
        }

        // Get six *working* days from now as the upper bound
        $workingDaysAdded = 0;
        $dtUpperBound = self::ImmutableToMutable($dtNow);
        while ($workingDaysAdded < 6) {
            if ($dtUpperBound->add(new DateInterval("P1D"))->format('N') <= 5 ) {
                $workingDaysAdded++;
            }
        }

        if ($dtUpperBound < $dtProposed) {
            return false;
        }

        return true;
    }

    private static function ImmutableToMutable(DateTimeImmutable $dt)
    {
        // Not using PHP 7.3 yet which has DateTime::createFromImmutable
        return DateTime::createFromFormat(
            DateTimeInterface::ATOM,
            $dt->format(DateTimeInterface::ATOM)
        );
    }
}