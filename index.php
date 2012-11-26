<?php

/**
* RewriteRule Generator
*
* @license MIT
* @author Jesse G. Donat <donatj@gmail.com> http://donatstudios.com
*
*/

/**
 * Sort an array based on the length of $array['query']
 * This function is used in usort.
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function sortByQueryLen($a, $b) {
	if (count($a['query']) == count($b['query'])) {
		return 0;
	}
	return (count($a['query']) > count($b['query'])) ? -1 : 1;
}

if( $_POST ) {
	$_POST['tabbed_rewrites'] = preg_replace('/(\t| )+/', '	', $_POST['tabbed_rewrites']); // Spacing Cleanup
	$lines = explode(PHP_EOL, $_POST['tabbed_rewrites'] );

	$rules = array();
	$i = -1;

	if( strlen(trim($_POST['tabbed_rewrites'])) ) {
		foreach( $lines as $line ) {
			$rule = array();
			$type = 0;
			$line = trim($line);
			if( $line == '' ) continue;
			$i++;
			$ab = explode("	", $line);

			if( count($ab) != 2 ) {
				$rule['error'] = '# MALFORMED LINE SKIPPED: ' . $line;
				$rules[$type][] = $rule;
				continue;
			}

			$ab0p = parse_url( trim($ab[0]) );
			$ab1p = parse_url( trim($ab[1]) );

			if( $_POST['desc_comments'] ) {
				$rule['0'] = '# '.$_POST['type'].' --- ' . $ab[0] . ' => ' . $ab[1];
			}

			if( $ab0p['host'] != $ab1p['host'] ) {
				$rule['httphost'] = 'RewriteCond %{HTTP_HOST} ^'.quotemeta($ab0p['host']).'$';
				$type = '3';
				$prefix = $ab1p['scheme'] . '://' . $ab1p['host'] . '/';
			}else{
				$prefix = '/';
				$type = '4';
			}

			$ab0pqs = explode('&', $ab0p['query']);
			if ($ab0pqs > 0 && strlen($ab0pqs[0]))
				$type = ($type === '3') ? '1' : '2';
			foreach( $ab0pqs as $qs ) {
				if( strlen( $qs ) > 0 ) {
					$rule['query'][] = 'RewriteCond %{QUERY_STRING} (^|&)'. quotemeta($qs) .'($|&)';
				}
			}

			$rule['rule'] = 'RewriteRule ^'.quotemeta(ltrim($ab0p['path'],'/')).'$ '.$prefix.ltrim( $ab1p['path'], '/' ).'?'.$ab1p['query'] . ( $_POST['type'] == 'Rewrite' ? '&%{QUERY_STRING}':' [L,R=301]' );
			$rules[$type][] = $rule;
		}
		if ($i !== -1) {
			ksort($rules);
			foreach ($rules as &$t) {
				usort($t, 'sortByQueryLen');
				foreach ($t as &$r) {
					if (array_key_exists('query', $r)) {
						$r['query'] = implode(PHP_EOL, $r['query']);
					}
					$r = implode(PHP_EOL, $r);
				}
				$t = implode(PHP_EOL . PHP_EOL, $t);
			}
			$rules = implode(PHP_EOL . PHP_EOL, $rules);
		}
	}
}else{
	$_POST['desc_comments'] = 1;
	$_POST['tabbed_rewrites'] = "http://www.test.com/test.html	http://www.test.com/spiders.html" . PHP_EOL . "http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html" . PHP_EOL . "text/faq.html?faq=20	helpdesk/kb.php";
}

?>
<form method="post" class="htaccess-rewrites">
	<textarea cols="100" rows="20" name="tabbed_rewrites" class="rewrite-field tabbed"><?php echo htmlentities( $_POST['tabbed_rewrites'] ) ?></textarea><br />
	<select name="type" class="method">
		<option>301</option>
		<option<?php echo $_POST['type'] == 'Rewrite' ? ' selected="selected"' : '' ?>>Rewrite</option>
	</select>
	<label><input type="checkbox" name="desc_comments" value="1"<?php echo $_POST['desc_comments'] ? ' checked="checked"' : '' ?> class="comments">Comments</label>
	<br />
	<textarea cols="100" rows="20" readonly="readonly" class="rewrite-field output"><?php echo htmlentities($rules) ?></textarea><br />
	<input type="submit" />
</form>