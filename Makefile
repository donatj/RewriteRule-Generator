.PHONY: test
test: cs
	./vendor/bin/phpunit

.PHONY: cs
cs:
	./vendor/bin/phpcs -s

.PHONY: cbf
cbf:
	./vendor/bin/phpcbf

