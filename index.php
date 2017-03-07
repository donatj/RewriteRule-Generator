<?php

/**
 * RewriteRule Generator
 *
 * @license MIT
 * @author Jesse G. Donat <donatj@gmail.com> https://donatstudios.com
 *
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

$errors = 0;
$output = '';

/**
 * @param string $from
 * @param string $to
 * @param bool   $show_comments
 * @return string
 */
function generateApacheRewrite( $from, $to ) {
	$parsedFrom = parse_url(trim($from));
	$parsedTo   = parse_url(trim($to));

	$line_output = "";


	if( !$parsedFrom['host'] && $parsedTo['host'] ) {
		throw new Exception('Unclear relative host. When the "FROM" URI specifies a HOST the "TO" MUST specify a HOST as well.');
	} elseif( $parsedFrom['host'] != $parsedTo['host'] && $parsedTo['host'] ) {
		$line_output .= 'RewriteCond %{HTTP_HOST} ^' . preg_quote($parsedFrom['host']) . '$';
		$line_output .= PHP_EOL;
		$prefix = $parsedTo['scheme'] . '://' . $parsedTo['host'] . '/';
	} else {
		$prefix = '/';
	}

	$explodedQuery = explode('&', $parsedFrom['query']);
	foreach( $explodedQuery as $qs ) {
		if( strlen($qs) > 0 ) {
			$line_output .= 'RewriteCond %{QUERY_STRING} (^|&)' . preg_quote($qs) . '($|&)';
			$line_output .= PHP_EOL;
		}
	}

	$line_output .= 'RewriteRule ^' . preg_quote(ltrim($parsedFrom['path'], '/')) . '$ ' . $prefix . ltrim($parsedTo['path'], '/') . '?' . $parsedTo['query'] . ($_POST['type'] == 'Rewrite' ? '&%{QUERY_STRING}' : ' [L,R=301]');
	$line_output .= PHP_EOL;

	return $line_output;
}

if( $_POST['tabbed_rewrites'] ) {
	$_POST['tabbed_rewrites'] = preg_replace('/(\t| )+/', '	', $_POST['tabbed_rewrites']); // Spacing Cleanup

	$lines = explode(PHP_EOL, $_POST['tabbed_rewrites']);

	if( strlen(trim($_POST['tabbed_rewrites'])) ) {
		foreach( $lines as $line ) {
			$line = trim($line);
			if( $line == '' ) continue;
			$explodedLine = explode("\t", $line);

			if( count($explodedLine) != 2 ) {
				$output .= PHP_EOL . '# ERROR: Malformed Line Skipped: ' . $line . PHP_EOL;
				$errors += 1;
				continue;
			}

			if( $_POST['desc_comments'] ) {
				$output .= PHP_EOL . '# ' . $_POST['type'] . ' --- ' . $explodedLine[0] . ' => ' . $explodedLine[1] . PHP_EOL;
			}

			try {
				$output .= generateApacheRewrite($explodedLine[0], $explodedLine[1]);
			} catch(Exception $e) {
				$output .= '# ERROR: ' . $e->getMessage() . ': ' . $line . PHP_EOL;
				$errors += 1;
			}
		}
	}

	if( $errors > 0 ) {
		$output = "# WARNING: Input contained {$errors} error(s)" . PHP_EOL . $output;
	}
} else {
	$_POST['desc_comments']   = 1;
	$_POST['tabbed_rewrites'] = <<<EOD
http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html
http://www.test3.com/faq.html?faq=13&layout=bob	bbq.html
text/faq.html?faq=20	helpdesk/kb.php
EOD;
}

?>
<script src="//ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools-yui-compressed.js"></script>
<script>
	window.addEvent('domready', function() {
		var insertAtCursor = function( myField, myValue ) {
			//IE support
			if( document.selection ) {
				myField.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
			}
			//MOZILLA/NETSCAPE support
			else if( myField.selectionStart || myField.selectionStart == '0' ) {
				var startPos = myField.selectionStart;
				var endPos = myField.selectionEnd;
				myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
				myField.selectionEnd = myField.selectionStart = startPos + myValue.length;
			} else {
				myField.value += myValue;
			}
		};

		var input = $('tsv-input');
		var output = $('rewrite-output');

		input.addEvent('keydown', function( e ) {
			if( e.key == 'tab' ) {
				e.stop();
				insertAtCursor(e.target, "\t");
			}
		});

		output.addEvent('click', function( e ) {
			e.target.select();
		});
	});
</script>
<form method="post">
	<textarea id="tsv-input" cols="100" rows="20" name="tabbed_rewrites" style="width: 100%; height: 265px;"><?php echo htmlentities($_POST['tabbed_rewrites']) ?></textarea><br />
	<select name="type">
		<option>301</option>
		<option<?php echo $_POST['type'] == 'Rewrite' ? ' selected="selected"' : '' ?>>Rewrite</option>
	</select>
	<label><input type="checkbox" name="desc_comments" value="1"<?php echo $_POST['desc_comments'] ? ' checked="checked"' : '' ?>>Comments</label>
	<br />
	<textarea id="rewrite-output" cols="100" rows="20" readonly="readonly" style="width: 100%; height: 265px;<?= $errors > 0 ? 'background: LightPink;' : '' ?>"><?php echo htmlentities($output) ?></textarea><br />
	<center><input type="submit" /></center>
</form>
