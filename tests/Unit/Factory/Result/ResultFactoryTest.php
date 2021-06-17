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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\Result;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultFactory;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class ResultFactoryTest extends TestCase
{
    /** @var ResultFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultFactory();
    }

    public function testCreateSuccess(): void
    {
        $data = [
            'id' => 'resultIdentifier',
            'scoreOf' => 'resultLineItemIdentifier',
            'userId' => 'resultUserIdentifier',
            'resultScore' => 10,
            'resultMaximum' => 100,
            'comment' => 'resultComment',
            'key' => 'value'
        ];

        $result = $this->subject->create($data);

        $this->assertInstanceOf(ResultInterface::class, $result);

        $this->assertEquals($data['userId'], $result->getUserIdentifier());
        $this->assertEquals($data['scoreOf'], $result->getLineItemIdentifier());
        $this->assertEquals($data['id'], $result->getIdentifier());
        $this->assertEquals($data['resultScore'], $result->getResultScore());
        $this->assertEquals($data['resultMaximum'], $result->getResultMaximum());
        $this->assertEquals($data['comment'], $result->getComment());
        $this->assertSame(['key' => 'value'], $result->getAdditionalProperties()->all());
    }

    public function testCreateFailureOnMissingUserIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory user identifier');

        $this->subject->create([]);
    }

    public function testCreateFailureOnMissingLineItemIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory line item identifier');

        $this->subject->create(
            [
                'userId' => 'resultUserIdentifier'
            ]
        );
    }
}
