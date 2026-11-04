<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor\Tests;

use Keboola\CuratedDataExtractor\StorageBrowser;
use Keboola\StorageApi\Client;
use PHPUnit\Framework\TestCase;

/**
 * Runs StorageBrowser through the real Storage client (URL composition included) against recorded table list pages,
 * so no token is needed.
 */
class StorageBrowserTableListTest extends TestCase
{
    public function testGetDataSetsReadsProviderMetadataAndFollowsPagination() : void
    {
        $client = new RecordingClient([
            $this->fixture('table-list-page-1.json'),
            $this->fixture('table-list-page-2.json'),
        ]);

        $data = (new StorageBrowser($client))->getDataSets();

        self::assertSame(
            [
                'v2/storage/branch/default/table-list?include=metadata',
                'v2/storage/branch/default/table-list?include=metadata&page[limit]=2&page[after]=eyJhIjoiYiJ9',
            ],
            $client->requestedUrls
        );
        self::assertSame(['in.c-curated.rates', 'in.c-curated.empty-description'], array_keys($data));
        self::assertSame(
            [
                'id' => 'in.c-curated.rates',
                'name' => 'Exchange rates',
                'description' => 'Currency exchange rates',
            ],
            $data['in.c-curated.rates']
        );
        self::assertNull($data['in.c-curated.empty-description']['description']);
    }

    private function fixture(string $name) : array
    {
        return json_decode((string) file_get_contents(__DIR__ . '/fixtures/' . $name), true);
    }
}
