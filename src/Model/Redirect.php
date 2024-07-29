<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Model;

readonly class Redirect
{
    public string $requestUri;
    public string $target;

    public function __construct(
        string $requestUri,
        string $target,
        public string $responseCode = '301'
    ) {
        assert(in_array($responseCode, ['301', '302']));

        $this->requestUri = '/'.ltrim(preg_replace('#^(https?://[^/]+)?#', '', trim($requestUri)), '/');
        $this->target = str_contains($target, '://') ? trim($target) : '/'.ltrim(trim($target), '/');
    }
}
