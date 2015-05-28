<?php
	

if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		if ($this->user_info['permission']['visit_question'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function index_action()
	{
		H::ajax_json_output(AWS_APP::RSM(array(
			'cookie' => $_COOKIE[G_COOKIE_PREFIX . '_user_login']
		), 1, null));
	}


}
?>

