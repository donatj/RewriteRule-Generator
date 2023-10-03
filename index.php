<?php

/**
 * RewriteRule Generator
 *
 * @license MIT
 * @author Jesse G. Donat <donatj@gmail.com> https://donatstudios.com/RewriteRule_Generator
 *
 */

use donatj\RewriteGenerator\ApacheModRewriteGenerator;
use donatj\RewriteGenerator\Engine;
use donatj\RewriteGenerator\RewriteTypes;

if( file_exists(__DIR__ . '/vendor/autoload.php') ) {
	require __DIR__ . '/vendor/autoload.php';
}

// Avoiding the composer autoloader momentarily for backwards compatibility
spl_autoload_register(function ( string $className ) {
	$parts = explode('\\', $className);
	array_shift($parts);
	array_shift($parts);
	$path = implode(DIRECTORY_SEPARATOR, $parts);

	require "src/{$path}.php";
});

$paramRewrites = $_POST['tabbed_rewrites'] ?? <<<EOD
http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html
http://www.test3.com/faq.html?faq=13&layout=bob	bbq.html
text/faq.html?faq=20	helpdesk/kb.php
EOD;
$paramComments = (bool)($_POST ? ($_POST['desc_comments'] ?? false) : true);
$paramType     = (int)($_POST['type'] ?? RewriteTypes::PERMANENT_REDIRECT);

$generator = new ApacheModRewriteGenerator;

$engine = new Engine($generator);
$output = $engine->generate($paramRewrites, $paramType, $paramComments);

?>
<!DOCTYPE html>
<form method="post">
	<textarea id="tsv-input" cols="100" rows="20" name="tabbed_rewrites" style="width: 100%; height: 265px;" title="TSV Input"><?php echo htmlentities($paramRewrites) ?></textarea><br />
	<select name="type" title="Rewrite Type">
            <option value="<?= RewriteTypes::TEMPORARY_REDIRECT ?>" <?php echo $paramType === RewriteTypes::TEMPORARY_REDIRECT ? ' selected' : '' ?>>302</option>
            <option value="<?= RewriteTypes::PERMANENT_REDIRECT ?>" <?php echo $paramType === RewriteTypes::PERMANENT_REDIRECT ? ' selected' : '' ?>>301</option>
            <option value="<?= RewriteTypes::SERVER_REWRITE ?>" <?php echo $paramType === RewriteTypes::SERVER_REWRITE ? ' selected' : '' ?>>Rewrite</option>
	</select>
	<label>
		<input type="checkbox" name="desc_comments" value="1"<?php echo $paramComments ? ' checked' : '' ?>>Comments
	</label>

	<br />

	<textarea id="rewrite-output" cols="100" rows="20" readonly="readonly" style="width: 100%; height: 265px;<?= $engine->getLastErrorCount() > 0 ? 'background: LightPink;' : '' ?>" title="Rewrite Output"><?php echo htmlentities($output) ?></textarea><br />

	<center>
		<input type="submit" />
	</center>
</form>
<script type="text/javascript">
	// window.addEventListener('domready', function() {
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

	var input = document.getElementById('tsv-input');
	var output = document.getElementById('rewrite-output');

	input.addEventListener('keydown', function( e ) {
		console.log(e.key);
		if( e.key.toLowerCase() === 'tab' ) {
			e.preventDefault();
			insertAtCursor(e.target, "\t");
		}
	});

	output.addEventListener('click', function( e ) {
		e.target.select();
	});
	// });
</script>