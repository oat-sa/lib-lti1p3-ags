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

namespace OAT\Library\Lti1p3Ags\Validator;

class LineItemValidator
{
    private $errors = [];

    /**
     * @throws ValidationException
     */
    public function validate(string $contextId, float $scoreMaximum, string $label): void
    {
        if (is_null($contextId)) {
            $this->errors = 'Missing context id';
        }

        if (is_float($scoreMaximum)) {
            $this->errors = 'ScoreMaximum must be a float';
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }

    /**
     * @throws ValidationException
     */
    public function validateFromArray(array $array): void
    {
        if (is_null($array['contextId'])) {
            $this->errors = 'Missing context id';
        }

        if (is_null($array['id'])) {
            $this->errors = 'Missing id';
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }
}
