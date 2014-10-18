# conditionals

ifeq '$(env)' 'ci'
  reporter = --tap
endif

# default

default:

# aliases

install: vendor

# tasks

clean:
	rm -rf vendor tmp/report

test: vendor/dev
	vendor/phpunit/phpunit/phpunit.php \
	  --configuration phpunit.xml $(reporter)

test-cov: vendor/dev
	vendor/phpunit/phpunit/phpunit.php \
	  --configuration phpunit.xml --coverage-html tmp/report

# targets

vendor: composer.json composer.lock
	composer install --no-dev
	touch $@

vendor/dev: composer.json composer.lock
	composer install
	touch $@

# phonies

.PHONY: default clean install test test-cov
