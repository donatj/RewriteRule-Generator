<?php

$data = "http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13	http://www.test.com/faqs.html?id=10
text/faq.html?faq=20	helpdesk/kb.php";

$lines = explode(PHP_EOL, $_POST['tabbed_rewrites'] );

$str = '';
if( strlen(trim($_POST['tabbed_rewrites'])) ) {
	foreach( $lines as $line ) {
		$ab = explode("	", $line);
		
		$ab0p = parse_url( trim($ab[0]) );
		$ab1p = parse_url( trim($ab[1]) );
		
		if( $_POST['desc_comments'] ) { $str .= PHP_EOL . '#   ---   ' . $ab[0] . ' => ' . $ab[1] . PHP_EOL; }
		
		$ab0pqs = explode('&', $ab0p['query']);
		foreach( $ab0pqs as $qs ) {
			if( strlen( $qs ) > 0 ) {
				$str .= 'RewriteCond %{QUERY_STRING} (^|&)'. quotemeta($qs) .'($|&)';
				$str .= PHP_EOL;
			}
		}
		
		$str .= 'RewriteRule ^'.quotemeta(ltrim($ab0p['path'],'/')).'$ '.ltrim( $ab1p['path'], '/' ).'?'.$ab1p['query'] . ( $_POST['type'] == 'Rewrite' ? '&%{QUERY_STRING}':' [L,R=301]' );
		$str .= PHP_EOL;
		
	}
	
	$data = $_POST['tabbed_rewrites'];
}

if( !$_POST ) {
	$_POST['desc_comments'] = 1;
}

?>
<form method="post">
	<textarea cols="100" rows="20" name="tabbed_rewrites"><?php echo htmlentities( $data ) ?></textarea><br />
	<select name="type">
		<option>301</option>
		<option<?php echo $_POST['type'] == 'Rewrite' ? ' selected="selected"' : '' ?>>Rewrite</option>
	</select>
	<label><input type="checkbox" name="desc_comments" value="1"<?php echo $_POST['desc_comments'] ? ' checked="checked"' : '' ?>>Comments</label>
	<br />
	<textarea cols="100" rows="20" readonly="readonly"><?php echo htmlentities($str) ?></textarea><br />
	<input type="submit">
</form>