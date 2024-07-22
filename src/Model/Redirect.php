<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Model;

class Redirect
{
    public readonly string $requestUri;
    public readonly string $target;

    public function __construct(
        string $requestUri,
        string $target,
        readonly string $responseCode = '301'
    ) {
        assert(in_array($responseCode, ['301', '302']));

        $this->requestUri = '/'.ltrim(preg_replace('#^(https?://[^/]+)?#', '', trim($requestUri)), '/');
        $this->target = str_contains($target, '://') ? trim($target) : '/'.ltrim(trim($target), '/');
    }
}
