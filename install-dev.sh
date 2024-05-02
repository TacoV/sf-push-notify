#!/bin/bash

# Run this file to get up and running in a fresh Github Codespace
echo "Setting up" $CODESPACE_NAME

wget https://get.symfony.com/cli/installer -O - | bash
mv /home/codespace/.symfony5/bin/symfony /usr/local/bin/symfony

sudo apt update
sudo apt upgrade -y
# sudo apt install php-intl php-opcache
sudo apt install php-pgsql
symfony check:requirements
symfony check:security

