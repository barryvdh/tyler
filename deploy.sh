#!/bin/bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento indexer:reindex
php bin/magento setup:static-content:deploy -f
php bin/magento c:c
php bin/magento c:f
sudo chmod -R 777 var/ pub/ generated
