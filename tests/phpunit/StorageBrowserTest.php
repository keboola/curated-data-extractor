<?php

declare(strict_types=1);
/** @noinspection PhpUnhandledExceptionInspection */

namespace Keboola\CuratedDataExtractor\Tests;

use Keboola\Csv\CsvFile;
use Keboola\CuratedDataExtractor\StorageBrowser;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Keboola\StorageApi\Metadata;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;

class StorageBrowserTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setup() : void
    {
        parent::setUp();
        if (empty(getenv('KBC_TEST_TOKEN')) || empty('KBC_TEST_URL')) {
            throw new \Exception("KBC_TEST_TOKEN or KBC_TEST_URL is empty");
        }
        $this->client = new Client([
            'token' => getenv('KBC_TEST_TOKEN'),
            'url' => getenv('KBC_TEST_URL'),
        ]);
        try {
            $this->client->dropBucket('in.c-curated-data-tests', ['force' => true]);
        } catch (ClientException $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }
        $this->client->createBucket('curated-data-tests', 'in', 'Curated data extractor tests');
    }

    public function testListTables() : void
    {
        $temp = new Temp();
        $temp->initRunFolder();
        $temp->getTmpFolder();
        foreach (['first', 'second', 'third'] as $table) {
            $csv = new CsvFile($temp->getTmpFolder() . $table);
            $csv->writeRow(['id', 'name']);
            $csv->writeRow(['1', $table]);
            $this->client->createTable('in.c-curated-data-tests', $table, $csv);
        }
        $metadata = new Metadata($this->client);
        $metadata->postTableMetadata(
            'in.c-curated-data-tests.second',
            'keboola.ex-curated-data',
            [
                [
                    'key' => 'KBC.name',
                    'value' => 'test table',
                ],
                [
                    'key' => 'KBC.description',
                    'value' => 'some description',
                ],
            ]
        );
        $metadata->postTableMetadata(
            'in.c-curated-data-tests.third',
            'keboola.ex-curated-data',
            [
                [
                    'key' => 'KBC.name',
                    'value' => 'third table',
                ],
            ]
        );

        $browser = new StorageBrowser($this->client);
        $data = $browser->getDataSets();
        self::assertCount(2, $data);
        self::assertEquals('in.c-curated-data-tests.second', $data['in.c-curated-data-tests.second']['id']);
        self::assertEquals('test table', $data['in.c-curated-data-tests.second']['name']);
        self::assertEquals('some description', $data['in.c-curated-data-tests.second']['description']);
        self::assertEquals('in.c-curated-data-tests.third', $data['in.c-curated-data-tests.third']['id']);
        self::assertEquals('third table', $data['in.c-curated-data-tests.third']['name']);
        self::assertEquals(null, $data['in.c-curated-data-tests.third']['description']);
    }

    public function testExportDataset() : void
    {
        $temp = new Temp();
        $temp->initRunFolder();
        $temp->getTmpFolder();
        foreach (['first', 'second', 'third'] as $table) {
            $csv = new CsvFile($temp->getTmpFolder() . $table);
            $csv->writeRow(['id', 'name']);
            $csv->writeRow(['1', $table]);
            $this->client->createTable('in.c-curated-data-tests', $table, $csv);
        }
        $metadata = new Metadata($this->client);
        $metadata->postTableMetadata(
            'in.c-curated-data-tests.second',
            'keboola.ex-curated-data',
            [
                [
                    'key' => 'KBC.name',
                    'value' => 'test table',
                ],
                [
                    'key' => 'KBC.description',
                    'value' => 'some description',
                ],
            ]
        );
        $metadata->postTableMetadata(
            'in.c-curated-data-tests.third',
            'keboola.ex-curated-data',
            [
                [
                    'key' => 'KBC.name',
                    'value' => 'third table',
                ],
            ]
        );

        $browser = new StorageBrowser($this->client);
        $data = $browser->getDataSets();
        self::assertCount(2, $data);
        self::assertEquals('in.c-curated-data-tests.second', $data['in.c-curated-data-tests.second']['id']);
        self::assertEquals('test table', $data['in.c-curated-data-tests.second']['name']);
        self::assertEquals('some description', $data['in.c-curated-data-tests.second']['description']);
        self::assertEquals('in.c-curated-data-tests.third', $data['in.c-curated-data-tests.third']['id']);
        self::assertEquals('third table', $data['in.c-curated-data-tests.third']['name']);
        self::assertEquals(null, $data['in.c-curated-data-tests.third']['description']);
    }
}
