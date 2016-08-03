<?php

namespace Keboola\CuratedDataExtractor;

use Keboola\CuratedDataExtractor\Exception\UserException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\TableExporter;

class Executor
{
    public function run()
    {
        $dataDir = getenv('KBC_DATADIR');

        $configFile = file_get_contents($dataDir . 'config.json');
        $config = json_decode($configFile, true);

        $action = $config['action'];
        $storageToken = $config['image_parameters']['storage_token'];
        if ($action == 'run') {
            if (count($config['storage']['output']['tables']) != 1) {
                throw new UserException("Exactly one table must be set in outputmaping.");
            }
            if (empty($config['parameters']['dataset'])) {
                throw new UserException("Dataset is a required parameter.");
            }
            if (empty($config['image_parameters']['storage_token'])) {
                throw new \Exception("storage_token must be set in image parameters.");
            }

            $outputFile = $config['storage']['output']['tables'][0]['source'];
            $dataSet = $config['parameters']['dataset'];
            echo "Getting dataset $dataSet.\n";
            $this->exportDataSet($storageToken, $dataSet, $dataDir, $outputFile);
            echo "Dataset obtained.\n";
        } elseif ($action == 'list') {
            $dataSets = $this->getDataSets($storageToken);
            echo json_encode(['dataSets' => $dataSets]);
        }
    }

    private function exportDataSet($storageToken, $dataSet, $dataDir, $outputFile)
    {
        $dataSets = $this->getDataSets($storageToken);
        if (!isset($dataSets[$dataSet])) {
            throw new UserException("Dataset $dataSet is not a known dataset.");
        }
        $client = new Client(['token' => $storageToken]);
        $tableExporter = new TableExporter($client);
        $tableExporter->exportTable(
            $dataSet,
            $dataDir . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . $outputFile,
            []
        );
    }

    private function getDataSets($storageToken)
    {
        $client = new Client(['token' => $storageToken]);
        $tables = $client->listTables(null, ['attributes']);
        $dataSets = [];
        foreach ($tables as $table) {
            $attribute = $this->getAttribute($table['attributes'], 'curated.data.name');
            if ($attribute) {
                $dataSets[$table['id']] = $attribute;
            }
        }
        return $dataSets;
    }

    private function getAttribute($attributes, $attributeName)
    {
        foreach ($attributes as $attribute) {
            if ($attribute['name'] == $attributeName) {
                return $attribute['value'];
            }
        }
        return null;
    }
}