<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Shield\Exceptions\ValidationException;
use ReflectionObject;
use ReflectionProperty;

trait CheckQueryReturnTrait
{
    protected ?bool $currentDBDebug = null;

    /**
     * @param bool|int|string $return insert() returns insert ID.
     */
    protected function checkQueryReturn($return): void
    {
        $this->restoreDBDebug();

        $this->checkValidationError();

        if ($return === false) {
            $error   = $this->db->error();
            $message = 'Query error: ' . $error['code'] . ', '
                . $error['message'] . ', query: ' . $this->db->getLastQuery();

            throw new DatabaseException($message, (int) $error['code']);
        }
    }

    protected function checkValidationError(): void
    {
        $validationErrors = $this->validation->getErrors();

        if ($validationErrors !== []) {
            $message = 'Validation error:';

            foreach ($validationErrors as $field => $error) {
                $message .= ' [' . $field . '] ' . $error;
            }

            throw new ValidationException($message);
        }
    }

    protected function disableDBDebug(): void
    {
        if (! $this->db->DBDebug) {
            // `DBDebug` is false. Do nothing.
            return;
        }

        $this->currentDBDebug = $this->db->DBDebug;

        $propertyDBDebug = $this->getPropertyDBDebug();
        $propertyDBDebug->setValue($this->db, false);
    }

    protected function restoreDBDebug(): void
    {
        if ($this->currentDBDebug === null) {
            // `DBDebug` has not been changed. Do nothing.
            return;
        }

        $propertyDBDebug = $this->getPropertyDBDebug();
        $propertyDBDebug->setValue($this->db, $this->currentDBDebug);

        $this->currentDBDebug = null;
    }

    protected function getPropertyDBDebug(): ReflectionProperty
    {
        $refClass    = new ReflectionObject($this->db);
        $refProperty = $refClass->getProperty('DBDebug');
        $refProperty->setAccessible(true);

        return $refProperty;
    }
}
