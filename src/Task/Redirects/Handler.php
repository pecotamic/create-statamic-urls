<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Task\Redirects;

use Pecotamic\CreateURLs\Model\Redirect;

interface Handler
{
    /**
     * @param  Redirect[]|array  $redirects
     */
    public function createAll(array $redirects);

    public function create(Redirect $redirect);

    public function reset();
}
