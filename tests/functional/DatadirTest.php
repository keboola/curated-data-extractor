<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor\Tests\Functional;

use Keboola\Csv\CsvFile;
use Keboola\DatadirTests\AbstractDatadirTestCase;
use Keboola\DatadirTests\DatadirTestSpecification;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Keboola\StorageApi\Metadata;

class DatadirTest extends AbstractDatadirTestCase
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

    public function testExtract() : void
    {
        $csv = new CsvFile(__DIR__ . '/basic-data/tables/some-table');
        $this->client->createTable('in.c-curated-data-tests', 'some-table', $csv);
        $metadata = new Metadata($this->client);
        $metadata->postTableMetadata(
            'in.c-curated-data-tests.some-table',
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

        $specification = new DatadirTestSpecification(
            null,
            0,
            null,
            null,
            __DIR__ . '/basic-data/'
        );
        $tempDatadir = $this->getTempDatadir($specification);
        $data = [
            'parameters' => [
                'dataset' => 'in.c-curated-data-tests.some-table',
            ],
            'image_parameters' => [
                '#storage_token' => getenv('KBC_TEST_TOKEN'),
                'storage_url' => getenv('KBC_TEST_URL'),
            ],
            'action' => 'run',
        ];
        file_put_contents($tempDatadir->getTmpFolder()  . '/config.json', json_encode($data));
        $process = $this->runScript($tempDatadir->getTmpFolder());

        $lines = explode("\n", trim(file_get_contents($tempDatadir->getTmpFolder() . '/out/tables/some-table')));
        $header = array_shift($lines);
        sort($lines);
        array_unshift($lines, $header);
        file_put_contents($tempDatadir->getTmpFolder() . '/out/tables/some-table', implode("\n", $lines) . "\n");
        $this->assertMatchesSpecification($specification, $process, $tempDatadir->getTmpFolder());
    }

    public function testExtractEmptyDataset() : void
    {
        $specification = new DatadirTestSpecification(
            null,
            1,
            'The path "root.parameters.dataset" cannot contain an empty value, but got "".'
        );
        $tempDatadir = $this->getTempDatadir($specification);
        $data = [
            'parameters' => [
                'dataset' => '',
            ],
            'image_parameters' => [
                '#storage_token' => getenv('KBC_TEST_TOKEN'),
                'storage_url' => getenv('KBC_TEST_URL'),
            ],
            'action' => 'run',
        ];
        file_put_contents($tempDatadir->getTmpFolder()  . '/config.json', json_encode($data));
        $process = $this->runScript($tempDatadir->getTmpFolder());
        $this->assertMatchesSpecification($specification, $process, $tempDatadir->getTmpFolder());
    }
}
