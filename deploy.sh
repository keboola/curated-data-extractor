#!/bin/bash
docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag keboola/curated-data-extractor quay.io/keboola/curated-data-extractor:$TRAVIS_TAG
docker tag keboola/curated-data-extractor quay.io/keboola/curated-data-extractor:latest
docker images
docker push quay.io/keboola/curated-data-extractor:$TRAVIS_TAG
docker push quay.io/keboola/curated-data-extractor:latest
