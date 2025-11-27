.PHONY: install test cs cs-fix cs-fixer psalm analyze qa

install:
	composer install --prefer-dist --no-progress --no-interaction

test:
	./vendor/bin/phpunit --testdox

cs:
	./vendor/bin/phpcs

cs-fix:
	./vendor/bin/phpcbf

cs-fixer:
	./vendor/bin/php-cs-fixer fix

psalm:
	./vendor/bin/psalm

analyze: cs psalm

qa: test analyze

