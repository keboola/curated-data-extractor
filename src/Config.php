<?php

declare(strict_types=1);

namespace Keboola\CuratedDataExtractor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getDataSet() : string
    {
        return $this->getValue(['parameters', 'dataset']);
    }

    public function getStorageToken() : string
    {
        return $this->getValue(['image_parameters', '#storage_token']);
    }

    public function getStorageUrl() : string
    {
        return $this->getValue(['image_parameters', 'storage_url']);
    }
}
