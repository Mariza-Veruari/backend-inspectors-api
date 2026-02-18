<?php

namespace App\Service;

use App\Entity\Inspector;

/**
 * Converts datetimes between inspector local timezone and UTC.
 * Allowed timezones: Europe/Madrid, America/Mexico_City, Europe/London.
 */
class TimezoneService
{
    public function __construct(
        private readonly array $allowedTimezones = Inspector::ALLOWED_TIMEZONES
    ) {
    }

    public function isAllowed(string $timezone): bool
    {
        return \in_array($timezone, $this->allowedTimezones, true);
    }

    /**
     * Convert a datetime string or object from inspector's local timezone to UTC.
     */
    public function toUtc(\DateTimeInterface|string $local, string $inspectorTimezone): \DateTimeImmutable
    {
        $this->assertAllowed($inspectorTimezone);
        $tz = new \DateTimeZone($inspectorTimezone);
        $utc = new \DateTimeZone('UTC');
        if (\is_string($local)) {
            $dt = new \DateTimeImmutable($local, $tz);
        } else {
            $dt = \DateTimeImmutable::createFromInterface($local)->setTimezone($tz);
        }
        return $dt->setTimezone($utc);
    }

    /**
     * Convert a UTC datetime to inspector's local timezone for API output.
     */
    public function fromUtc(\DateTimeInterface $utc, string $inspectorTimezone): \DateTimeImmutable
    {
        $this->assertAllowed($inspectorTimezone);
        $dt = \DateTimeImmutable::createFromInterface($utc);
        return $dt->setTimezone(new \DateTimeZone($inspectorTimezone));
    }

    /**
     * Format UTC datetime for JSON in inspector's local timezone (ISO 8601).
     */
    public function formatForInspector(\DateTimeInterface $utc, string $inspectorTimezone): string
    {
        return $this->fromUtc($utc, $inspectorTimezone)->format(\DateTimeInterface::ATOM);
    }

    private function assertAllowed(string $timezone): void
    {
        if (!$this->isAllowed($timezone)) {
            throw new \InvalidArgumentException(sprintf(
                'Timezone "%s" is not allowed. Allowed: %s',
                $timezone,
                implode(', ', $this->allowedTimezones)
            ));
        }
    }
}
