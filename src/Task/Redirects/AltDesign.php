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

class AltDesign implements Handler
{
    private Manager $manager;

    public function __construct()
    {
        $this->manager = new Manager();

        if (!$this->manager->disk()->exists('content/alt-redirect')) {
            $this->manager->disk()->makeDirectory('content/alt-redirect');
        }
    }

    public function reset(): void
    {
        collect(File::allFiles(base_path('/content/alt-redirect')))
            ->each(function ($file) {
                File::delete($file->getPathname());
            });
    }

    public function createAll(array $redirects): void
    {
        collect($redirects)
            ->each(function (Redirect $redirect) {
                $this->create($redirect);
            });
    }

    public function create(Redirect $redirect): void
    {
        $data = [
            'from' => $redirect->requestUri,
            'redirect_type' => $redirect->responseCode,
            'to' => $redirect->target,
            'sites' => ['default'],
        ];

        $this->manager->disk()->put('content/alt-redirect/'.hash('sha512', (base64_encode($data['from']))).'.yaml', Yaml::dump($data));
    }
}
