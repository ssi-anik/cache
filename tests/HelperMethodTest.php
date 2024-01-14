<?php

use Anik\Cache\Exception\InvalidArgumentException;
use Anik\Cache\Test\BaseTestCase;

class HelperMethodTest extends BaseTestCase
{
    public function expiryTimestampMethodDataProvider(): array
    {
        $time = new DateTimeImmutable();
        $interval = DateInterval::createFromDateString('-1 seconds');

        return [
            'with null value' => [null, null, false],
            'with exact value' => [$time, $time->getTimestamp(), false],
            'with date interval value' => [$interval, $time->getTimestamp() - 1, false],
            'with string value' => ['string', null, true],
        ];
    }

    /** @dataProvider expiryTimestampMethodDataProvider */
    public function testExpiryTimestampMethod($time, $expected, $expectsException)
    {
        if ($expectsException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $value = expiry_timestamp($time);
        if (!$expectsException) {
            if (is_null($value)) {
                $this->assertNull($value);
            } else {
                $this->assertEqualsWithDelta($expected, $value, 2);
            }
        }
    }

    public function testIntervalToSeconds()
    {
        $first = new DateTimeImmutable('2024-01-14 10:11:12');
        $second = new DateTimeImmutable('2024-01-14 12:14:16');

        $this->assertSame(-100, interval_to_seconds(DateInterval::createFromDateString('-100 seconds')));
        $this->assertSame(200, interval_to_seconds(DateInterval::createFromDateString('200 seconds')));
        $this->assertSame(7384, interval_to_seconds($first->diff($second)));
        $this->assertSame(-7384, interval_to_seconds($second->diff($first)));
    }

    public function testGetParameterType()
    {
        $this->assertSame(InvalidArgumentException::class, get_parameter_type(new InvalidArgumentException()));
        $this->assertSame('string', get_parameter_type('string value'));
        $this->assertSame('integer', get_parameter_type(123456));
    }
}
