<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\TableExporter;

class Component extends BaseComponent
{
    /**
     * @var Client
     */
    private $client;

    public function run(): void
    {
        $action = $this->getConfig()->getAction();
        /** @var Config $config */
        $config = $this->getConfig();
        $this->client = new Client([
            'token' => $config->getValue(['image_parameters', '#storage_token']),
            'url' => $config->getValue(['image_parameters', 'storage_url']),
        ]);

        if ($action == 'run') {
            $dataSet = $config->getDataSet();
            // use only table name as file name
            $outName = substr($dataSet, strrpos($dataSet, '.') + 1);
            echo "Getting dataset $dataSet.\n";
            $this->exportDataSet($dataSet, $outName);
            echo "Dataset obtained.\n";
        } elseif ($action == 'list') {
            $dataSets = (new StorageBrowser($this->client))->getDataSets();
            echo \GuzzleHttp\json_encode(['datasets' => $dataSets]);
        } else {
            throw new UserException("Invalid action: " . $action);
        }
    }

    private function exportDataSet(string $dataSet, string $outputFile) : void
    {
        $dataSets = (new StorageBrowser($this->client))->getDataSets();
        if (!isset($dataSets[$dataSet])) {
            throw new UserException("Dataset $dataSet is not a known dataset.");
        }
        $tableExporter = new TableExporter($this->client);
        $tableExporter->exportTable(
            $dataSet,
            $this->getDataDir() . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR .
            'tables' . DIRECTORY_SEPARATOR . $outputFile,
            []
        );
    }


    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
