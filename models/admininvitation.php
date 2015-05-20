<?php

if (!defined('IN_ANWSION'))
{
	die;
}

class admininvitation_class extends AWS_MODEL
{

	public function get_unique_invitation_code()
	{
		$invitation_code = md5(uniqid(rand(), true) . fetch_salt(4));

		if ($this->fetch_row('admininvitation', "invitation_code = '" . $this->quote($invitation_code) . "'"))
		{
			return $this->get_unique_invitation_code();
		}
		else
		{
			return $invitation_code;
		}
	}

	public function add_invitation($invitation_code, $add_time, $total = 1, $num = 1)
	{
		return  $this->insert('admininvitation', array(
			'invitation_code' => $invitation_code,
			'add_time' => $add_time,
			'total' => $total,
			'num' => $num
			));
	}

	public function check_code_available($invitation_code)
	{
		return $this->fetch_row('admininvitation', "num > 0 AND invitation_code = '" . $this->quote($invitation_code) . "'");
	}

	public function use_invitation_code($invitation_code)
	{
		// echo "string";
		return $this->query("UPDATE ". $this->get_table('admininvitation')." SET num = num - 1 WHERE invitation_code = '". $this->quote($invitation_code). "'");
	}
}
?>