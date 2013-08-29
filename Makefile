.PHONY: all install install-dev test test-tap test-coverage

CONFIG_FILE = $(shell pwd)/phpunit.xml
PU = phpunit --configuration ${CONFIG_FILE}

all: install

install:
	composer install

install-dev:
	composer install --dev

test-tap:
	${PU} --tap

test-coverage:
	${PU} --coverage-html tmp/report

test:
        ifeq '$(env)' 'ci'
		$(PU) --tap
        else
		$(PU)
        endif