<?php

namespace tests;

use donatj\RewriteGenerator\ApacheModRewriteGenerator;
use donatj\RewriteGenerator\Engine;
use donatj\RewriteGenerator\RewriteTypes;
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

	public function exampleProvider() {
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
	}

}
