<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor;

use Keboola\StorageApi\Client;
use Keboola\StorageApi\Metadata;

class StorageBrowser
{
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
        $metadata = new Metadata($this->client);
        $tables = $this->client->listTables(null);
        $dataSets = [];
        foreach ($tables as $table) {
            $tableMetadata = $metadata->listTableMetadata($table['id']);
            $name = $this->getMetadataValue($tableMetadata, 'keboola.ex-curated-data', 'KBC.name');
            $description = $this->getMetadataValue($tableMetadata, 'keboola.ex-curated-data', 'KBC.description');
            if ($name) {
                $dataSets[$table['id']] = [
                    'id' => $table['id'],
                    'name' => $name,
                    'description' => $description
                ];
            }
        }
        return $dataSets;
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
