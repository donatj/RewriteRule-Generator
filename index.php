<?php

/**
 * RewriteRule Generator
 *
 * @license MIT
 * @author Jesse G. Donat <donatj@gmail.com> http://donatstudios.com
 *
 */

error_reporting(E_ALL ^ E_NOTICE);

$output = '';

if( $_POST['tabbed_rewrites'] ) {
	$_POST['tabbed_rewrites'] = preg_replace('/(\t| )+/', '	', $_POST['tabbed_rewrites']); // Spacing Cleanup
	$lines                    = explode(PHP_EOL, $_POST['tabbed_rewrites']);

	if( strlen(trim($_POST['tabbed_rewrites'])) ) {
		foreach( $lines as $line ) {
			$line = trim($line);
			if( $line == '' ) continue;
			$explodedLine = explode("	", $line);

			if( count($explodedLine) != 2 ) {
				$output .= PHP_EOL . '# MALFORMED LINE SKIPPED: ' . $line . PHP_EOL;
				continue;
			}

			$parsedFrom = parse_url(trim($explodedLine[0]));
			$parsedTo   = parse_url(trim($explodedLine[1]));

			if( $_POST['desc_comments'] ) {
				$output .= PHP_EOL . '# ' . $_POST['type'] . ' --- ' . $explodedLine[0] . ' => ' . $explodedLine[1] . PHP_EOL;
			}

			if( $parsedFrom['host'] != $parsedTo['host'] ) {
				$output .= 'RewriteCond %{HTTP_HOST} ^' . quotemeta($parsedFrom['host']) . '$';
				$output .= PHP_EOL;
				$prefix = $parsedTo['scheme'] . '://' . $parsedTo['host'] . '/';
			} else {
				$prefix = '/';
			}

			$explodedQuery = explode('&', $parsedFrom['query']);
			foreach( $explodedQuery as $qs ) {
				if( strlen($qs) > 0 ) {
					$output .= 'RewriteCond %{QUERY_STRING} (^|&)' . quotemeta($qs) . '($|&)';
					$output .= PHP_EOL;
				}
			}

			$output .= 'RewriteRule ^' . quotemeta(ltrim($parsedFrom['path'], '/')) . '$ ' . $prefix . ltrim($parsedTo['path'], '/') . '?' . $parsedTo['query'] . ($_POST['type'] == 'Rewrite' ? '&%{QUERY_STRING}' : ' [L,R=301]');
			$output .= PHP_EOL;
		}
	}
} else {
	$_POST['desc_comments']   = 1;
	$_POST['tabbed_rewrites'] = <<<EOD
http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html
text/faq.html?faq=20	helpdesk/kb.php
EOD;
}

?>
<form method="post">
	<textarea cols="100" rows="20" name="tabbed_rewrites" style="width: 100%; height: 265px;"><?php echo htmlentities($_POST['tabbed_rewrites']) ?></textarea><br />
	<select name="type">
		<option>301</option>
		<option<?php echo $_POST['type'] == 'Rewrite' ? ' selected="selected"' : '' ?>>Rewrite</option>
	</select>
	<label><input type="checkbox" name="desc_comments" value="1"<?php echo $_POST['desc_comments'] ? ' checked="checked"' : '' ?>>Comments</label>
	<br />
	<textarea cols="100" rows="20" readonly="readonly" style="width: 100%; height: 265px;"><?php echo htmlentities($output) ?></textarea><br />
	<center><input type="submit" /></center>
</form>