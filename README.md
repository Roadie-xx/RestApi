# RestApi
Just another (simple) Rest Api framework.

## Inspiration
- https://medium.com/@ldudaraliyanage/php-crud-operations-with-mysql-and-html-bootstrap-2022-d4aca5569b6a  
- https://medium.com/@omer_14630/building-a-simple-crud-api-with-php-and-mysql-a-step-by-step-guide-aa31f9ab5a3b

## Testing
use https://github.com/Automattic/phpunit-docker

### Run tests
```bash
docker run -ti -v LOCAL_PROJECT_DIR:/var/www/composer ghcr.io/devgine/composer-php:latest sh
# For  example
docker run -ti -v ${pwd}:/var/www/composer ghcr.io/devgine/composer-php:v2-php7.4-alpine sh

# Updrade global packages in docker
    composer global upgrade

# Install packages
    composer install

# Available tests in this docker (@see https://github.com/devgine/composer-php) 
    # PHP Copy Past Detector
    phpcpd ./src

    # PHP Coding Standards Fixer
    php-cs-fixer check -v ./src
    php-cs-fixer check -v --diff ./src

    # PHPStan
    phpstan analyze --level=7 ./src

    # PHP Unit
    simple-phpunit --bootstrap=vendor/autoload.php ./tests
    simple-phpunit --coverage-text --whitelist=./src  --bootstrap=vendor/autoload.php ./tests
```
