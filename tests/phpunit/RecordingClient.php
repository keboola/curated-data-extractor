<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor\Tests;

use Keboola\StorageApi\Client;

/**
 * Storage client that answers from recorded responses instead of the network and keeps the request URLs.
 */
class RecordingClient extends Client
{
    /** @var string[] */
    public $requestedUrls = [];

    /** @var array[] */
    private $responses;

    public function __construct(array $responses)
    {
        parent::__construct(['url' => 'https://connection.example.com', 'token' => 'dummy']);
        $this->responses = $responses;
    }

    // The parent signature is untyped, so the overriding method cannot declare types.
    // phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration
    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @param string|null $responseFileName
     * @param bool $handleAsyncTask
     * @return array
     */
    protected function request(
        $method,
        $url,
        $options = [],
        $responseFileName = null,
        $handleAsyncTask = true
    ) {
        $this->requestedUrls[] = $url;
        return array_shift($this->responses);
    }
    // phpcs:enable SlevomatCodingStandard.TypeHints.TypeHintDeclaration
}
