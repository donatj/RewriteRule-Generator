.PHONY: test
test: cs phpstan
	./vendor/bin/phpunit

.PHONY: cs
cs:
	./vendor/bin/phpcs -s

.PHONY: cbf
cbf:
	./vendor/bin/phpcbf

.PHONY: phpstan
phpstan:
	./vendor/bin/phpstan
