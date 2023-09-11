#!/bin/bash
git pull origin main
php bin/console cache:clear --env=prod --no-debug