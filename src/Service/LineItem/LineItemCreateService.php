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

namespace OAT\Library\Lti1p3Ags\Service\LineItem;

use OAT\Library\Lti1p3Ags\Model\LineItem;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepository;
use OAT\Library\Lti1p3Ags\Validator\LineItemValidator;
use OAT\Library\Lti1p3Ags\Validator\ValidationException;
use OAT\Library\Lti1p3Ags\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class LineItemCreateService implements LineItemCreateServiceInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ValidatorInterface  */
    private $validator;

    /** @var LineItemRepository */
    private $repository;

    /**
     * @todo use specific validator for Creation process
     */
    public function __construct(
        LineItemRepository $repository,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function create(LineItem $lineItem): void
    {
        // Check if id is null, required data are here e.g. scoreMaximum, datetime... not null
        $this->validator->validate($lineItem);

        $this->repository->create($lineItem);
    }
}
