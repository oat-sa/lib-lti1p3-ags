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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Serializer;

use JsonSerializable;
use RuntimeException;

class JsonSerializer implements JsonSerializerInterface
{
    public function serialize(JsonSerializable $object): string
    {
        $json = json_encode($object);
        $this->assertNoJsonError();

        return $json;
    }

    public function deserialize(string $json): array
    {
        $data = json_decode($json, true);
        $this->assertNoJsonError();

        return $data;
    }

    /**
     * @throws RuntimeException
     */
    private function assertNoJsonError(): void
    {
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException(json_last_error_msg());
        }
    }
}
