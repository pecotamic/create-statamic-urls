<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Task\Redirects;

use Illuminate\Support\Facades\File;
use Pecotamic\CreateURLs\Model\Redirect;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\YAML;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Filesystem\Manager;

class Pecotamic implements Handler
{
    private $globals;

    public function __construct()
    {
        assert($this->globals = GlobalSet::findByHandle('pecotamic_redirects'));
    }

    public function reset()
    {
        $variables = $this->globals->inDefaultSite();

        $variables->data(['redirects' => []]);
        $this->globals->save();
    }

    public function createAll(array $redirects)
    {
        $variables = $this->globals->inDefaultSite();

        $redirects = collect($redirects)
            ->map(function (Redirect $redirect) {
                return [
                    'type' => 'redirect',
                    'request_uri' => $redirect->requestUri,
                    'match_type' => 'exact',
                    'response_code' => $redirect->responseCode,
                    'target' => $redirect->target,
                ];
            })
            ->values()
            ->all();

        $variables->redirects = [
            ...array_map(fn (Values $values) => array_map(fn (Value|string $value) => is_string($value) ? $value : $value->raw(), array_filter($values->all())),
                $variables->redirects),
            ... $redirects
        ];

        $this->globals->save();
    }

    public function create(Redirect $redirect)
    {
        $this->createAll([$redirect]);
    }
}
