<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Task;

use Pecotamic\CreateURLs\Model\Redirect;
use Pecotamic\CreateURLs\Task\Redirects\AltDesign;
use Pecotamic\CreateURLs\Task\Redirects\Handler;
use Pecotamic\CreateURLs\Task\Redirects\Pecotamic;

class Redirects implements Handler
{
    /**
     * @var Handler[]|array
     */
    private array $handlers = [];

    public function __construct()
    {
        if (class_exists(\Pecotamic\Redirect\Data\Data::class)) {
            $this->handlers[] = new Pecotamic();
        }
        if (class_exists(\AltDesign\AltRedirect\Helpers\Data::class)) {
            $this->handlers[] = new AltDesign();
        }

        assert(!empty($this->handlers));
    }

    /**
     * @param  Redirect[]|array  $redirects
     */
    public function createAll(array $redirects): void
    {
        foreach ($this->handlers as $handler) {
            $handler->createAll($redirects);
        }
    }

    public function create(Redirect $redirect): void
    {
        foreach ($this->handlers as $handler) {
            $handler->create($redirect);
        }
    }

    public function reset(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->reset();
        }
    }
}
