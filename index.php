<?php

require 'vendor/autoload.php';

use donatj\RewriteGenerator\ApacheModRewriteGenerator;
use donatj\RewriteGenerator\Engine;
use donatj\RewriteGenerator\RewriteTypes;

/**
 * RewriteRule Generator
 *
 * @license MIT
 * @author Jesse G. Donat <donatj@gmail.com> https://donatstudios.com/RewriteRule_Generator
 *
 */
if( !$_POST ) {
	$_POST['desc_comments']   = 1;
	$_POST['tabbed_rewrites'] = <<<EOD
http://www.test.com/test.html	http://www.test.com/spiders.html
http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html
http://www.test3.com/faq.html?faq=13&layout=bob	bbq.html
text/faq.html?faq=20	helpdesk/kb.php
EOD;
	$_POST['type']            = RewriteTypes::PERMANENT_REDIRECT;
}

$errors = 0;
$engine = new Engine(new ApacheModRewriteGenerator);
$output = $engine->generate($_POST['tabbed_rewrites'], $_POST['type'], isset($_POST['desc_comments']), $errors);

?>
<script>
	window.addEventListener('domready', function() {
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
			if( e.key == 'tab' ) {
				e.stop();
				insertAtCursor(e.target, "\t");
			}
		});

		output.addEventListener('click', function( e ) {
			e.target.select();
		});
	});
</script>
<form method="post">
	<textarea id="tsv-input" cols="100" rows="20" name="tabbed_rewrites" style="width: 100%; height: 265px;" title="TSV Input"><?php echo htmlentities($_POST['tabbed_rewrites']) ?></textarea><br />
	<select name="type" title="Rewrite Type">
		<option value="<?= RewriteTypes::PERMANENT_REDIRECT ?>">301</option>
		<option value="<?= RewriteTypes::SERVER_REWRITE ?>" <?php echo $_POST['type'] == RewriteTypes::SERVER_REWRITE ? ' selected="selected"' : '' ?>>
			Rewrite
		</option>
	</select>
	<label>
		<input type="checkbox" name="desc_comments" value="1"<?php echo isset($_POST['desc_comments']) ? ' checked="checked"' : '' ?>>Comments
	</label>

	<br />

	<textarea id="rewrite-output" cols="100" rows="20" readonly="readonly" style="width: 100%; height: 265px;<?= $errors > 0 ? 'background: LightPink;' : '' ?>" title="Rewrite Output"><?php echo htmlentities($output) ?></textarea><br />

	<center>
		<input type="submit" />
	</center>
</form>
