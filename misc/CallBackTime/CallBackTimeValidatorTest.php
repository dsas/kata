<?php

include_once 'vendor/autoload.php';
include_once 'CallBackTimeTimeValidator.php';

class CallBackValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function test_NotEnoughNotice()
    {
        $dtNow = new DateTimeImmutable("Monday 9am");
        $dtCallback = $dtNow->add(new DateInterval("PT15M"));

        $bValid = CallBackTimeValidator::Validate($dtCallback, $dtNow);

        $this->assertFalse($bValid, "Callback must be at least two hours in the future");
    }

    public function test_EnoughNotice()
    {
        $dtNow = new DateTimeImmutable("Monday 1PM");
        $dtCallback = $dtNow->add(new DateInterval("PT2H"));

        $bValid = CallBackTimeValidator::Validate($dtCallback, $dtNow);

        $this->assertTrue($bValid, "Callback is more than two hours in the future");
    }

    public function test_tooFarAway()
    {
        $dtNow = new DateTimeImmutable("Monday 9am");
        $dtRequested = $dtNow->add(new DateInterval("P12D"));

        $valid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

        $this->assertFalse($valid, "Callback 12 days ahead are invalid");
    }

    public function test_NotTooFarAway()
    {
        $dtNow = new DateTimeImmutable("Monday 9am");

        foreach ([
            1,   // Tuesday
            2,   // Wednesday
            3,   // Thursday
            4,   // Friday
            7,   // Monday
            8,   // Tuesday
                 ] as $futureDay) {

            $dtRequested = $dtNow->add(new DateInterval("P${futureDay}D"));

            $valid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

            $this->assertTrue($valid, "Callback must be no more than six working days away, $futureDay is more");
        }
    }

    public function test_NoCallsBeforeOpening()
    {
        $openingTimes = [
            'Monday' => '9am',
            'Tuesday' => '9am',
            'Wednesday' => '9am',
            'Thursday' => '9am',
            'Friday' => '9am',
        ];

        foreach ($openingTimes as $day => $time) {
            $dtNow = new DateTimeImmutable("$day $time");
            $dtRequested = $dtNow->sub(new DateInterval("PT3H"));

            $bValid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

            $this->assertFalse($bValid, "Callback must be at or after $time on $day");
        }
    }

    public function test_CallsAfterOpening()
    {
        $openingTimes = [
            'Monday' => '9am',
            'Tuesday' => '9am',
            'Wednesday' => '9am',
            'Thursday' => '9am',
            'Friday' => '9am',
        ];

        foreach ($openingTimes as $day => $time) {
            $dtNow = new DateTimeImmutable("$day $time");
            $dtRequested = $dtNow->add(new DateInterval("PT2H15M"));

            $bValid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

            $this->assertTrue($bValid, "Callback after $time on $day is ok");
        }
    }

    public function test_NoCallsAfterClosing()
    {
        $closingTimes = [
            'Monday' => '5pm',
            'Tuesday' => '5pm',
            'Wednesday' => 'noon',
            'Thursday' => '5pm',
            'Friday' => '5pm',
        ];

        foreach ($closingTimes as $day => $time) {
            $dtNow = new DateTimeImmutable("$day $time");
            $dtRequested = (new DateTimeImmutable("$day $time"))->add(new DateInterval("PT15M"));

            $bValid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

            $this->assertFalse($bValid, "Callback must be before $time on $day");
        }
    }

    public function test_CallsBeforeClosing()
    {
        $closingTimes = [
            'Monday' => '5pm',
            'Tuesday' => '5pm',
            'Wednesday' => 'noon',
            'Thursday' => '5pm',
            'Friday' => '5pm',
        ];

        foreach ($closingTimes as $day => $time) {
            $dtNow = new DateTimeImmutable("$day 9am");
            $dtRequested = (new DateTimeImmutable("$day $time"))->sub(new DateInterval("PT15M"));

            $bValid = CallBackTimeValidator::Validate($dtRequested, $dtNow);

            $this->assertTrue($bValid, "Callback before $time on $day should be ok");
        }
    }

}
