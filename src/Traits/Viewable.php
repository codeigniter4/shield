<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Traits;

trait Viewable
{
    /**
     * Provides a way for third-party systems to simply override
     * the way the view gets converted to HTML to integrate with their
     * own templating systems.
     */
    protected function view(string $view, array $data = [], array $options = []): string
    {
        return view($view, $data, $options);
    }
}
