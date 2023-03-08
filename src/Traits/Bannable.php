<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Traits;

trait Bannable
{
    /**
     * Is the user banned?
     */
    public function isBanned(): bool
    {
        return (bool) ($this->status && $this->status === 'banned');
    }

    /**
     * Ban the user from logging in.
     *
     * @return $this
     */
    public function ban(?string $message = null): self
    {
        $this->status         = 'banned';
        $this->status_message = $message;

        $users = auth()->getProvider();

        $users->save($this);

        return $this;
    }

    /**
     * Unban the user and allow them to login
     *
     * @return $this
     */
    public function unBan(): self
    {
        $this->banned         = null;
        $this->banned_message = null;

        $users = auth()->getProvider();

        $users->save($this);

        return $this;
    }

    /**
     * Returns the ban message.
     */
    public function getBanMessage(): ?string
    {
        return $this->status_message;
    }
}
