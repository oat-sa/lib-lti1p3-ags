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

namespace OAT\Library\Lti1p3Ags\Model\Result;

class Result implements ResultInterface
{
    /** @var string */
    private $id;

    /** @var string */
    private $userId;

    /** @var float */
    private $resultScore;

    /** @var int */
    private $resultMaximum;

    /** @var string */
    private $comment;

    /** @var string */
    private $scoreOf;

    public function __construct(
        string $id,
        string $userId,
        float $resultScore,
        int $resultMaximum,
        string $comment,
        string $scoreOf
    )
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->resultScore = $resultScore;
        $this->resultMaximum = $resultMaximum;
        $this->comment = $comment;
        $this->scoreOf = $scoreOf;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getResultScore(): float
    {
        return $this->resultScore;
    }

    public function getResultMaximum(): int
    {
        return $this->resultMaximum;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getScoreOf(): string
    {
        return $this->scoreOf;
    }
}
