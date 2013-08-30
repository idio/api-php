# variables

PHP_UNIT = vendor/phpunit/phpunit/phpunit.php
CONFIG_FILE = $(PWD)/phpunit.xml
TEST_CMD = $(PHP_UNIT) --configuration ${CONFIG_FILE}

# aliases

all: install
install: vendor
test-all: test-cov

# targets

clean:
	rm -rf vendor tmp/report

vendor: composer.json composer.lock
	composer install --no-dev

$(PHP_UNIT): composer.json composer.lock
	composer install --dev

test: $(PHP_UNIT)
        ifeq '$(env)' 'ci'
		$(TEST_CMD) --tap
        else
		$(TEST_CMD)
        endif

test-cov: $(PHP_UNIT)
	$(TEST_CMD) --coverage-html tmp/report

# phonies

.PHONY: all clean install test test-all test-cov
