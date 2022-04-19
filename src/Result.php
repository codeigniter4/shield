<?php

namespace CodeIgniter\Shield;

use CodeIgniter\Shield\Interfaces\Authenticatable;

class Result
{
    protected bool $success = false;

    /**
     * Provides a simple explanation of
     * the error that happened.
     * Typically a single sentence.
     */
    protected ?string $reason = null;

    /**
     * Extra information.
     *
     * @var Authenticatable|string|null `Authenticatable` when successful. Suggestion strings when fails.
     */
    protected $extraInfo;

    /**
     * @phpstan-param array{success: bool, reason?: string|null, extraInfo?: string|Authenticatable} $details
     * @psalm-param array{success: bool, reason?: string|null, extraInfo?: string|Authenticatable} $details
     */
    public function __construct(array $details)
    {
        foreach ($details as $key => $value) {
            assert(property_exists($this, $key), 'Property "' . $key . '" does not exist.');

            $this->{$key} = $value;
        }
    }

    /**
     * Was the result a success?
     */
    public function isOK(): bool
    {
        return $this->success;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return Authenticatable|string|null `Authenticatable` when successful. Suggestion strings when fails.
     */
    public function extraInfo()
    {
        return $this->extraInfo;
    }
}
