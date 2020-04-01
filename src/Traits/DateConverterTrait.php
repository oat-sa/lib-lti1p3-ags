<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use LogicException;
use Throwable;

trait DateConverterTrait
{
    /**
     * @param DateTimeInterface|string|null $date
     */
    public function convertIntoDateTime($date): ?DateTimeInterface
    {
        if (is_string($date)) {
            return $this->iso8601ToDate($date);
        }

        if ($date instanceof DateTimeInterface) {
            return $date;
        }

        return null;

    }

    public function iso8601ToDate(string $iso8601Date): DateTimeInterface
    {
        try {
            return Carbon::createFromFormat(DateTimeInterface::ATOM, $iso8601Date);
        } catch (Throwable $exception) {
            throw new LogicException('The string parameter provided must be ISO-8601 formatted');
        }
    }

    public function dateToIso8601(DateTimeInterface $date): string
    {
        return $date->format(DateTimeInterface::ATOM);
    }
}