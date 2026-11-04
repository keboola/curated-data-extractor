<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor;

use Keboola\StorageApi\Client;

class StorageBrowser
{
    private const API_PATH_PREFIX = '/v2/';

    private const TABLE_LIST_PATH = 'storage/branch/default/table-list?include=metadata';

    private const METADATA_PROVIDER = 'keboola.ex-curated-data';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getDataSets() : array
    {
        $dataSets = [];
        foreach ($this->listTables() as $table) {
            $tableMetadata = $table['metadata'] ?? [];
            $name = $this->getMetadataValue($tableMetadata, self::METADATA_PROVIDER, 'KBC.name');
            $description = $this->getMetadataValue($tableMetadata, self::METADATA_PROVIDER, 'KBC.description');
            if ($name) {
                $dataSets[$table['id']] = [
                    'id' => $table['id'],
                    'name' => $name,
                    'description' => $description,
                ];
            }
        }
        return $dataSets;
    }

    /**
     * Lists all tables with their metadata through the cursor-paginated table list.
     */
    private function listTables() : array
    {
        $tables = [];
        $path = self::TABLE_LIST_PATH;
        while ($path !== null) {
            $page = $this->client->apiGet($path);
            foreach ($page['data'] as $table) {
                $tables[] = $table;
            }
            $next = $page['links']['next'] ?? null;
            $path = $next === null ? null : $this->toStoragePath($next);
        }
        return $tables;
    }

    /**
     * Reduces an absolute `links.next` URL to the path and query the client prefixes with its API version.
     */
    private function toStoragePath(string $url) : string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $position = strpos($path, self::API_PATH_PREFIX);
        if ($position === false) {
            throw new \UnexpectedValueException(sprintf('Unexpected pagination link in the table list: %s', $url));
        }
        $relative = substr($path, $position + strlen(self::API_PATH_PREFIX));
        return $query === null ? $relative : $relative . '?' . $query;
    }

    private function getMetadataValue(array $metadata, string $provider, string $key) : ?string
    {
        foreach ($metadata as $item) {
            if (($item['provider'] == $provider) && ($item['key'] == $key)) {
                return $item['value'];
            }
        }
        return null;
    }
}
