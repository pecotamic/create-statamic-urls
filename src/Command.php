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
        $redirectHandler = new Redirects();
        $pagesHandler = new Pages();

        $this->question("Please enter the data with line format: <url> [<target> [<301|302>]]");

        $in = fopen('php://stdin', 'rb');

        if ($this->option('reset')) {
            (new Pages())->reset();
            (new Redirects())->reset();
        }

        while ($line = fgets($in)) {
            if (!preg_match('#^((?:https?://)?[-._/a-z0-9]+)(?:\s+((?:https?://)?[-._/a-z0-9]+)(?:\s+(301|302))?)?$#', trim($line), $matches)) {
                $this->warn('Invalid line');
                continue;
            }

            if (isset($matches[2])) {
                $redirectHandler->create(new Redirect($matches[1], $matches[2], $matches[3] ?? '301'));
            } else {
                $pagesHandler->create($matches[1]);
            }
        }
    }
}
