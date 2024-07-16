<?php

namespace Pecotamic\CreateURLs;

use Illuminate\Console\Command as BaseCommand;
use Pecotamic\CreateURLs\Model\Redirect;
use Pecotamic\CreateURLs\Task\Pages;
use Pecotamic\CreateURLs\Task\Redirects;
use Statamic\Console\RunsInPlease;

class Command extends BaseCommand
{
    use RunsInPlease;

    protected $signature = 'statamic:create-urls
        {--reset : Delete existing entries beforehand}
    ';

    protected $description = 'Setup statamic pages and redirects based on text from stdin';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $handlers = [
            'redirect' => new Redirects(),
            'pages' => new Pages(),
        ];

        $this->question("Please enter the data with line format: <url> [<target> [<301|302>]]");

        $handlersToReset = $this->option('reset') ? ['pages', 'redirects'] : [];

        $in = fopen('php://stdin', 'rb');
        while ($line = fgets($in)) {
            if (!preg_match('#^((?:https?://)?[-._/a-z0-9]+)(?:\s+((?:https?://)?[-._/a-z0-9]+)(?:\s+(301|302))?)?$#', trim($line), $matches)) {
                $this->warn('Invalid line');
                continue;
            }

            $handlerId = isset($matches[2]) ? 'redirect' : 'pages';
            $handler = $handlers[$handlerId];

            if ($index = array_search($handlerId, $handlersToReset, true)) {
                $handler->reset();
                unset($handlersToReset[$index]);
            }

            if (isset($matches[2])) {
                $handler->create(new Redirect($matches[1], $matches[2], $matches[3] ?? '301'));
            } else {
                $handler->create($matches[1]);
            }
        }
    }
}
