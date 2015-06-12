<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class preaccount_class extends AWS_MODEL
{
	
	/**
     * 用户登录验证
     *
     * @param string
     * @param string
     * @return array
     */
    public function check_login($email, $password)
    {
        if (!$email OR !$password)
        {
            return false;
        }

        if (H::valid_email($email))
        {
            $previous_uid = $this->is_exist($email, $password);
        }

        if (! $previous_uid)
        {
            return false;
        }
        else
        {
        	$init_user_name = $email;
        	$uid = $this->previous_user_register($init_user_name, $password, $email);
            return $uid;
        }

    }

    /**
     * 通过邮箱判断是否存在该老用户
     *
     * $attrb 为是否获取附加表信息, $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return int
     */
    public function is_exist($email, $password, $cache_result = true)
    {
        if (!$email)
        {
            return false;
        }

        $password = md5($password);
        if ($uid = $this->fetch_one('previous_users', 'id', "email = '" . $this->quote($email) . "' AND password = '" . $this->quote($password)."'" ))
        {
            // return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
            return $uid;
        }
    }

    /**
     * 老用户数据
     *
     * @param string
     * @param string
     * @param string
     * @param int
     * @param string
     * @return int
     */
    public function insert_previous_user($user_name, $password, $email = null, $sex = 0, $mobile = null)
    {
        if (!$user_name OR !$password)
        {
            return false;
        }

        if ($this->check_username($user_name))
        {
            return false;
        }

        if ($email AND $user_info = $this->get_user_info_by_email($email, false))
        {
            return false;
        }

        $salt = fetch_salt(4);

        if ($uid = $this->insert('users', array(
            'user_name' => htmlspecialchars($user_name),
            'password' => compile_password($password, $salt),
            'salt' => $salt,
            'avatar_file' => $rand_index,
            'email' => htmlspecialchars($email),
            'sex' => intval($sex),
            'mobile' => htmlspecialchars($mobile),
            'reg_time' => time(),
            'reg_ip' => ip2long(fetch_ip()),
            'email_settings' => serialize(get_setting('new_user_email_setting'))
        )))
        {
            $this->insert('users_attrib', array(
                'uid' => $uid
            ));

            $this->update_notification_setting_fields(get_setting('new_user_notification_setting'), $uid);

            //$this->model('search_fulltext')->push_index('user', $user_name, $uid);

                    //加入随机头像
            $rand_index =rand(1,14);
            if ($rand_index < 10) $rand_index="0".$rand_index;
            $rand_index = "000/00/00/i".$rand_index."_avatar_";

            $uid_t = sprintf("%09d", $uid);
            $dir1 = substr($uid_t, 0, 3);
            $dir2 = substr($uid_t, 3, 2);
            $dir3 = substr($uid_t, 5, 2);

            $path = get_setting('upload_dir') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid_t, - 2) . '_avatar_';
            copy(get_setting('upload_dir') . '/avatar/'.$rand_index."min.jpg", $path."min.jpg");
            copy(get_setting('upload_dir') . '/avatar/'.$rand_index."mid.jpg", $path."mid.jpg");
            copy(get_setting('upload_dir') . '/avatar/'.$rand_index."max.jpg", $path."max.jpg");
            copy(get_setting('upload_dir') . '/avatar/'.$rand_index."real.jpg", $path."real.jpg");
            $this->update_users_fields(array('avatar_file' => $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid_t, - 2) . '_avatar_'."min.jpg"), $uid);
        }

        return $uid;
    }

    /**
     * 后台自动注册老用户
     *
     * @param string
     * @param string
     * @param string
     * @return int
     */
    public function previous_user_register($user_name, $password = null, $email = null)
    {
         if ($uid = $this->insert_previous_user($user_name, $password, $email))
        {
            if ($def_focus_uids_str = get_setting('def_focus_uids'))
            {
                $def_focus_uids = explode(',', $def_focus_uids_str);

                foreach ($def_focus_uids as $key => $val)
                {
                    $this->model('follow')->user_follow_add($uid, $val);
                }
            }

            $this->update('users', array(
                'group_id' => 4,
                'reputation_group' => 5,
                'invitation_available' => get_setting('newer_invitation_num'),
                'is_first_login' => 1, 
                //设置valid_email为1，绕过邮箱验证
                'valid_email' => 1
            ), 'uid = ' . intval($uid));

            $this->model('integral')->process($uid, 'REGISTER', get_setting('integral_system_config_register'), '初始资本');
        }

        return $uid;
    }

    /**
     * 检查用户名是否已经存在
     *
     * @param string
     * @return boolean
     */
    public function check_username($user_name)
    {
        return $this->fetch_one('users', 'uid', "user_name = '" . $this->quote(trim($user_name)) . "' OR url_token = '" . $this->quote(trim($user_name)) . "'");
    }

    /**
     * 通过用户邮箱获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @return array
     */
    public function get_user_info_by_email($email, $cache_result = true)
    {
        if (!$email)
        {
            return false;
        }

        if ($uid = $this->fetch_one('users', 'uid', "email = '" . $this->quote($email) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    /**
     * 更新用户通知设置
     *
     * @param  array
     * @param  int
     * @return boolean
     */
    public function update_notification_setting_fields($data, $uid)
    {
        if (!$this->count('users_notification_setting', 'uid = ' . intval($uid)))
        {
            $this->insert('users_notification_setting', array(
                'data' => serialize($data),
                'uid' => intval($uid)
            ));
        }
        else
        {
            $this->update('users_notification_setting', array(
                'data' => serialize($data)
            ), 'uid = ' . intval($uid));
        }

        return true;
    }

    /**
     * 更新用户表字段
     *
     * @param array
     * @param uid
     * @return int
     */
    public function update_users_fields($update_data, $uid)
    {
        return $this->update('users', $update_data, 'uid = ' . intval($uid));
    }


}