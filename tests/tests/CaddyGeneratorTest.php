<?php

namespace tests;

use donatj\RewriteGenerator\CaddyGenerator;
use donatj\RewriteGenerator\Engine;
use donatj\RewriteGenerator\RewriteTypes;
use PHPUnit\Framework\TestCase;

class CaddyGeneratorTest extends TestCase {

	public function test_redirect_and_unique_matchers() : void {
		$engine = new Engine(new CaddyGenerator);
		$output = $engine->generate("/faq.html?faq=13&layout=bob\t/faqs.html\n/old\t/new", RewriteTypes::PERMANENT_REDIRECT, false);

		$this->assertSame(<<<'CADDY'
@generated1 {
	path_regexp ^/faq\.html$
	query faq=13 layout=bob
}
redir @generated1 /faqs.html 301

@generated2 {
	path_regexp ^/old$
}
redir @generated2 /new 301
CADDY
			. "\n", $output);
		$this->assertSame(0, $engine->getLastErrorCount());
	}

	public function test_rewrite_keeps_original_query() : void {
		$engine = new Engine(new CaddyGenerator);
		$output = $engine->generate("/old\t/new?added=yes", RewriteTypes::SERVER_REWRITE, false);
		$this->assertStringContainsString('rewrite @generated1 /new?added=yes&{query}', $output);
	}

	public function test_redirect_to_another_host() : void {
		$engine = new Engine(new CaddyGenerator);
		$output = $engine->generate("https://old.example/old\thttps://new.example/new", RewriteTypes::PERMANENT_REDIRECT, false);
		$this->assertStringContainsString("\thost old.example\n", $output);
		$this->assertStringContainsString('redir @generated1 https://new.example/new 301', $output);
	}

	public function test_unsupported_input_is_reported() : void {
		$engine = new Engine(new CaddyGenerator);
		$output = $engine->generate("/old?a=1&a=2\t/new", RewriteTypes::PERMANENT_REDIRECT, false);
		$this->assertStringContainsString('Unsupported or repeated query parameter', $output);
		$this->assertSame(1, $engine->getLastErrorCount());
	}

}
