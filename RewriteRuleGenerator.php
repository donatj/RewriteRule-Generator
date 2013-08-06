<?php
/**
 * RewriteRule Generator
 *
 * @license MIT
 * @author Jesse G. Donat <donatj@gmail.com> http://donatstudios.com
 * @author Anton J.P. Evers <ajpevers@gmail.com>
 *
 * Assumes a form with these elements:
 * tabbed_rewrites	string			tab separated string, see setDemoData()
 * type				301|Rewrite		rewrite method
 * desc_comments	[1]				show comments in output
 * always_show_host	[1]				use HTTP_HOST on same-domain rewrites
 */
class RewriteRuleGenerator
{
	// Strings used for building output
	const HTTP_HOST		= 'RewriteCond %%{HTTP_HOST} ^%s$';
	const QUERY_STRING	= 'RewriteCond %%{QUERY_STRING} (^|&)%s($|&)';
	const RULE_301		= 'RewriteRule ^%s$ %s%s?%s [L,R=301]';
	const RULE_REWRITE	= 'RewriteRule ^%s$ %s%s?%s&%%{QUERY_STRING}';
	const COMMENT		= '# %s --- %s => %s';
	const ERROR			= '# MALFORMED LINE SKIPPED: %s';

	// Private variables
	protected $_post;
	protected $_lines = array();
	protected $_rules;
	protected $_ruleType;

	/**
	 * Build mod_rewrite rules from a tab separated file with source and
	 * destination url's.
	 *
	 * @return bool|string
	 */
	public function getRules()
	{
		if (!$this->_getPost())
			return false;
		$this->_setLines();
		$this->_setMethod();
		foreach ($this->_lines as $line) {
			if ($this->_isMalformed($line))
				continue;
			$this->_buildRule($line);
		}
		$this->_sortRules();
		return $this->_rules;
	}

	/**
	 * Load demo data into the $_POST to display on a normal page load.
	 */
	public function setDemoData()
	{
		$_POST['desc_comments'] = 1;
		$_POST['tabbed_rewrites'] =
			"http://www.test.com/test.html	http://www.test.com/spiders.html" .
			PHP_EOL .
			"http://www.test.com/faq.html?faq=13&layout=bob	http://www.test2.com/faqs.html" .
			PHP_EOL .
			"text/faq.html?faq=20	helpdesk/kb.php";
	}

	/**
	 * Build a mod_rewrite rule from a source and target array. The result is
	 * saved in the $this->_rules array, grouped by type.
	 *
	 * These types are:
	 * - 0: comment
	 * - 1: a rule, a host and query string(s)
	 * - 2: a rule and query string(s) no host
	 * - 3: a rule, a host, no query string
	 * - 4: just a rule, no host, no query string
	 *
	 * Rules will appear in the output in this order. This way a rule without
	 * query strings cannot overrule the same rule with query strings.
	 *
	 * Rules with the most query strings will be placed over those with less
	 * query strings
	 *
	 * @param $line
	 */
	protected function _buildRule($line)
	{
		$source		= parse_url($line[0]);
		$target		= parse_url($line[1]);
		$queries 	= isset($source['query']) ? explode('&', $source['query']) : 0;
		$type		= 3;
		$rule		= array();

		if ($this->_post['desc_comments'])
			$rule['comment']	= $this->_getComment($line[0], $line[1]);

		if (isset($source['host']) && isset ($target['host'])) {
			if ($source['host'] != $target['host'] ||
				($this->_post['always_show_host'] && isset($source['host']))) {
				$rule['httphost']	= sprintf(self::HTTP_HOST, quotemeta($source['host']));
				$prefix				= $target['scheme'] . '://' . $target['host'] . '/';
			} else {
				$type				= 4;
				$prefix				= '/';
			}
		}

		if (count($queries) > 0 && strlen($queries[0])) {
			$type = ($type === 3) ? 1 : 2;
			foreach ($queries as $query) {
				if (strlen($query) > 0) {
					$rule['query'][] = sprintf(self::QUERY_STRING, quotemeta($query));
				}
			}
		}

		$rule['rule'] = sprintf($this->_ruleType,
			quotemeta(ltrim($source['path'], '/')), isset($prefix) ? $prefix : '',
			ltrim( $target['path'], '/' ), isset($target['query']) ? $target['query'] : '');

		$this->_rules[$type][] = $rule;
	}

	/**
	 * Sort rules in this order:
	 * - 0: comment
	 * - 1: a rule, a host and query string(s)
	 * - 2: a rule and query string(s) no host
	 * - 3: a rule, a host, no query string
	 * - 4: just a rule, no host, no query string
	 *
	 * Rules will appear in the output in this order. This way a rule without
	 * query strings cannot overrule the same rule with query strings.
	 *
	 * Rules with the most query strings will be placed over those with less
	 * query strings
	 */
	protected function _sortRules()
	{
		ksort($this->_rules);
		foreach ($this->_rules as &$rule) {
			usort($rule, array('RewriteRuleGenerator', '_sortByQueryLen'));
			foreach ($rule as &$condition) {
				if (array_key_exists('query', $condition))
					$condition['query'] = implode(PHP_EOL, $condition['query']);
				$condition = implode(PHP_EOL, $condition);
			}
			$rule = implode(PHP_EOL . PHP_EOL, $rule);
		}
		$this->_rules = implode(PHP_EOL . PHP_EOL, $this->_rules);
	}

	/**
	 * Retrieve and format the post data for use in this class
	 *
	 * @return array|bool
	 */
	protected function _getPost()
	{
		if (isset($this->_post))
			return $this->_post;
		$this->_post = false;
		if ($_POST && strlen(trim($_POST['tabbed_rewrites'])))
			$this->_post = array(
				'always_show_host'	=> isset($_POST['always_show_host']),
				'desc_comments'		=> isset($_POST['desc_comments']),
				'tabbed_rewrites'	=> explode(PHP_EOL, $_POST['tabbed_rewrites']),
				'type'				=> $_POST['type'],
			);
		return $this->_post;
	}

	/**
	 * Split the tab separated input string into a multidimensional array with
	 * source and destination url's
	 */
	protected function _setLines()
	{
		foreach ($this->_post['tabbed_rewrites'] as $line) {
			$line = preg_replace('/(\r|\n)+/', '', $line);
			$this->_lines[] = explode("\t", preg_replace('/(\t| )+/', '	', trim($line)));
		}
	}

	/**
	 * Validate a source and destination line. If the validation fails, a
	 * comment line is generated that will ALWAYS show in the output.
	 *
	 * @param $line
	 *
	 * @return bool
	 */
	protected function _isMalformed($line)
	{
		if ($line[0] === '')
			return true;
		if (count($line) != 2) {
			$this->_rules[0][] = array(
				'error' => sprintf(
					self::ERROR, implode(' ', $line)
				)
			);
			return true;
		}
		return false;
	}

	/**
	 * Build a comment string for the source - target comments
	 *
	 * @param $source
	 * @param $target
	 *
	 * @return string
	 */
	protected function _getComment($source, $target)
	{
		return sprintf(self::COMMENT,
			$_POST['type'],
			$source,
			$target);
	}

	/**
	 * Define which kind of rule will be used as output template. As if now that
	 * may be Redirect or 301
	 *
	 * @return bool|string
	 */
	protected function _setMethod()
	{
		switch ($this->_post['type']) {
			case '301':
				$this->_ruleType = self::RULE_301;
				break;
			case 'Rewrite':
				$this->_ruleType = self::RULE_REWRITE;
				break;
		}
		return false;
	}

	/**
	 * Sort an array based on the length of $array['query']
	 * This function is used in usort.
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	protected function _sortByQueryLen($a, $b) {
		if (count($a['query']) == count($b['query']))
			return 0;
		return (count($a['query']) > count($b['query'])) ? -1 : 1;
	}
}