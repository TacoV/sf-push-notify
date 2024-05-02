#!/bin/bash

# Run this file to get up and running in a fresh Github Codespace
echo "Setting up" $CODESPACE_NAME

wget https://get.symfony.com/cli/installer -O - | bash
mv /home/codespace/.symfony5/bin/symfony /usr/local/bin/symfony

sudo apt update
sudo apt upgrade -y
symfony check:requirements
symfony check:security
