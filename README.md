# Curated Data Extractor

[![Build Status](https://travis-ci.org/keboola/google-drive-writer.svg?branch=master)](https://travis-ci.org/keboola/google-drive-writer)

This extractor extracts prepared data sets from KBC Shared Data project (452) and is registered as `keboola.ex-curated.data` docker component. Prepared
data sets are data independent on customer, which are preloaded in KBC (e.g Currency exchange rates)

## Usage

### List Datasets
Run this as a synchronous docker action - do a `POST` to
`https://syrup.keboola.com/docker/keboola.ex-curated.data/action/list`

Request has no parameters, so request body should be:

```
{
    "configData": {}
}
```

Sample response: 

```
{
  "datasets": {
    "in.c-ex-currency.rates": {
      "id": "in.c-ex-currency.rates",
      "name": "Currency Rates (EUR)",
      "description": null
    },
    "in.c-ex-currency.rates-usd": {
      "id": "in.c-ex-currency.rates-usd",
      "name": "Currency Rates (USD)",
      "description": "Currency exchange rates from USD to all other currencies"
    }
  }
}
```

### Load dataset
Runs as standard asynchronous docker action - do a `POST` to
`https://syrup.keboola.com/docker/keboola.ex-curated.data/run`

With [standard request body](https://developers.keboola.com/extend/common-interface/config-file/) e.g.:
```
{
    "configData": {
        "storage": {
            "output": {
                "tables": [
                    {
                        "source": "source.csv",
                        "destination": "out.c-curated-data.currency"
                    }
                ]
            }
        },
        "parameters": {
            "dataset": "in.c-ex-currency.rates"
        }
    }
}
```

## Adding new datasets

- Upload a table to project Shared Data (452).
- Add table attribute `curated.data.name` with dataset name of your choice
- Optionally add table attribute `curated.data.description`
- Add the dataset to the UI template in component configuration:
```
      "enum": [
        "in.c-ex-currency.rates",
        "in.c-ex-currency.rates-usd"
      ],
      "options": {
        "enum_titles": [
          "Currency Rates (EUR)",
          "Currency Rates (USD)"
        ]
      }
```
