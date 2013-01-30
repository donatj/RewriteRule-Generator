<?php
include('RewriteRuleGenerator.php');

$ruleGen = new RewriteRuleGenerator();

if (!$rules = $ruleGen->getRules())
	$ruleGen->setDemoData();
?>
<form method="post" class="htaccess-rewrites">
    <textarea cols="100" rows="20" name="tabbed_rewrites" class="rewrite-field tabbed"><?php echo htmlentities($_POST['tabbed_rewrites']) ?></textarea>
	<br/>
    <select name="type" class="method">
		<option<?php echo $_POST['type'] == '301' ? ' selected="selected"' : '' ?>>301</option>
		<option<?php echo $_POST['type'] == 'Rewrite' ? ' selected="selected"' : '' ?>>Rewrite</option>
    </select>
    <label><input type="checkbox" name="desc_comments" value="1"<?php echo $_POST['desc_comments'] ? ' checked="checked"' : '' ?> class="comments">Comments</label>
    <label><input type="checkbox" name="always_show_host" value="1"<?php echo $_POST['always_show_host'] ? ' checked="checked"' : '' ?> class="host">Always add HTTP_HOST (f.i. multi-domain sites)</label>
    <br/>
    <textarea cols="100" rows="20" readonly="readonly" class="rewrite-field output"><?php echo htmlentities($rules) ?></textarea><br/>
    <input type="submit"/>
</form>