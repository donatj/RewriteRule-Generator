<?php

namespace tests;

use donatj\RewriteGenerator\ApacheModRewriteGenerator;
use donatj\RewriteGenerator\Engine;
use donatj\RewriteGenerator\RewriteTypes;
use Generator;
use PHPUnit\Framework\TestCase;

class ApacheIntegrationTest extends TestCase {

	/**
	 * @dataProvider exampleProvider
	 */
	public function test_examples( string $input, string $output301, string $outputRewrite ) {
		$engine = new Engine(new ApacheModRewriteGenerator);

		$given = $engine->generate($input, RewriteTypes::PERMANENT_REDIRECT, true);
		$this->assertSame(0, $engine->getLastErrorCount());
		$this->assertSame($output301, $given);

		$given = $engine->generate($input, RewriteTypes::SERVER_REWRITE, true);
		$this->assertSame(0, $engine->getLastErrorCount());
		$this->assertSame($outputRewrite, $given);
	}

	public function exampleProvider() : Generator {
		yield [
			<<<'TAG'
http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html
http://www.test3.com/faq.html?faq=13&layout=bob	bbq.html
text/faq.html?faq=20	helpdesk/kb.php
TAG
			, <<<'TAG'
# 301 --- http://www.test.com/test.html => http://www.test.com/spiders.html
RewriteRule ^test\.html$ /spiders.html? [L,R=301]

# 301 --- http://www.test.com/faq.html?faq=13&layout=bob => http://www.test2.com/faqs.html
RewriteCond %{HTTP_HOST} ^www\.test\.com$
RewriteCond %{QUERY_STRING} (^|&)faq\=13($|&)
RewriteCond %{QUERY_STRING} (^|&)layout\=bob($|&)
RewriteRule ^faq\.html$ http://www.test2.com/faqs.html? [L,R=301]

# 301 --- http://www.test3.com/faq.html?faq=13&layout=bob => bbq.html
RewriteCond %{QUERY_STRING} (^|&)faq\=13($|&)
RewriteCond %{QUERY_STRING} (^|&)layout\=bob($|&)
RewriteRule ^faq\.html$ /bbq.html? [L,R=301]

# 301 --- text/faq.html?faq=20 => helpdesk/kb.php
RewriteCond %{QUERY_STRING} (^|&)faq\=20($|&)
RewriteRule ^text/faq\.html$ /helpdesk/kb.php? [L,R=301]

TAG
			, <<<'TAG'
# Rewrite --- http://www.test.com/test.html => http://www.test.com/spiders.html
RewriteRule ^test\.html$ /spiders.html?&%{QUERY_STRING}

# Rewrite --- http://www.test.com/faq.html?faq=13&layout=bob => http://www.test2.com/faqs.html
RewriteCond %{HTTP_HOST} ^www\.test\.com$
RewriteCond %{QUERY_STRING} (^|&)faq\=13($|&)
RewriteCond %{QUERY_STRING} (^|&)layout\=bob($|&)
RewriteRule ^faq\.html$ http://www.test2.com/faqs.html?&%{QUERY_STRING}

# Rewrite --- http://www.test3.com/faq.html?faq=13&layout=bob => bbq.html
RewriteCond %{QUERY_STRING} (^|&)faq\=13($|&)
RewriteCond %{QUERY_STRING} (^|&)layout\=bob($|&)
RewriteRule ^faq\.html$ /bbq.html?&%{QUERY_STRING}

# Rewrite --- text/faq.html?faq=20 => helpdesk/kb.php
RewriteCond %{QUERY_STRING} (^|&)faq\=20($|&)
RewriteRule ^text/faq\.html$ /helpdesk/kb.php?&%{QUERY_STRING}

TAG
			,

		];

		yield [
			'http://www.site.ru/index.php/images/images/file/images/images/index.php	/test/',
			'# 301 --- http://www.site.ru/index.php/images/images/file/images/images/index.php => /test/
RewriteRule ^index\.php/images/images/file/images/images/index\.php$ /test/? [L,R=301]
',
			'# Rewrite --- http://www.site.ru/index.php/images/images/file/images/images/index.php => /test/
RewriteRule ^index\.php/images/images/file/images/images/index\.php$ /test/?&%{QUERY_STRING}
',
		];

		yield [
			'http://foo.html	http://bar.html',
			<<<'TAG'
# 301 --- http://foo.html => http://bar.html
RewriteCond %{HTTP_HOST} ^foo\.html$
RewriteRule ^$ http://bar.html/? [L,R=301]

TAG
			,
			<<<'TAG'
# Rewrite --- http://foo.html => http://bar.html
RewriteCond %{HTTP_HOST} ^foo\.html$
RewriteRule ^$ http://bar.html/?&%{QUERY_STRING}

TAG
			,
		];
	}

	/**
	 * @dataProvider failureProvider
	 */
	public function test_failures( string $input, string $output, int $errorCount ) {
		$engine = new Engine(new ApacheModRewriteGenerator);

		$given = $engine->generate($input, RewriteTypes::PERMANENT_REDIRECT, true);
		$this->assertSame($errorCount, $engine->getLastErrorCount());
		$this->assertSame($output, $given);
	}

	public function failureProvider() : Generator {
		yield [
			'a	b	c',
			<<<'TAG'
# WARNING: Input contained 1 error(s)

# ERROR: Malformed Line Skipped: a	b	c

TAG
			, 1,
		];

		yield [
			'a.html	http://bar.html',
			<<<'TAG'
# WARNING: Input contained 1 error(s)

# 301 --- a.html => http://bar.html
# ERROR: Unclear relative host. When the "FROM" URI specifies a HOST the "TO" MUST specify a HOST as well.: a.html	http://bar.html

TAG
			, 1,
		];

		yield [
			<<<'TAG'
foo.html	bar.html

baz.html	boo	bar.html

this	line	is	just	silly!

is	ok
BAD

is fine
TAG
			,
			<<<'TAG'
# WARNING: Input contained 3 error(s)

# 301 --- foo.html => bar.html
RewriteRule ^foo\.html$ /bar.html? [L,R=301]

# ERROR: Malformed Line Skipped: baz.html	boo	bar.html

# ERROR: Malformed Line Skipped: this	line	is	just	silly!

# 301 --- is => ok
RewriteRule ^is$ /ok? [L,R=301]

# ERROR: Malformed Line Skipped: BAD

# 301 --- is => fine
RewriteRule ^is$ /fine? [L,R=301]

TAG
			, 3,
		];
	}

}
