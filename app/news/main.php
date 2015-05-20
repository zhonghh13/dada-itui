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
			$rule_action['actions'][] = 'square';
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function index_action()
	{

	}

	public function square_action()
	{
		if (is_mobile())
		{
			HTTP::redirect('/m/news/');
		}
		
		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		$article_list = $this->model('article')->get_articles_list($category_info['id'], $_GET['page'], get_setting('contents_per_page'), 'add_time DESC');

		$article_list_total = $this->model('article')->found_rows();

		if ($article_list)
		{
			foreach ($article_list as $key => $value) 
			{
				if ($value['is_headline'])
				{
					$headline =  $value;
				}
			}
		}

		if ($headline)
		{
			$headline_ids[] = $headline['id'];
			$headline_uids[$headline['uid']] = $headline['uid'];
			$headline_topics = $this->model('topic')->get_topics_by_item_ids($headline_ids, 'article');
			$headline_user_info = $this->model('account')->get_user_info_by_uids($headline_uids, TRUE);
			echo "string".$headline_user_info;
			$headline['user_info'] = $headline_user_info[$headline['uid']];
		}

		if ($article_list)
		{
			foreach ($article_list as $key => $value) 
			{
				if ($value['is_recommend'] != '2')
				{
					unset($article_list[$key]);
					$article_list_total--;
				}
			}
		}

		if ($article_list)
		{
			foreach ($article_list AS $key => $val)
			{
				$article_ids[] = $val['id'];

				$article_uids[$val['uid']] = $val['uid'];
			}

			$article_topics = $this->model('topic')->get_topics_by_item_ids($article_ids, 'article');
			$article_users_info = $this->model('account')->get_user_info_by_uids($article_uids, TRUE);

			foreach ($article_list AS $key => $val)
			{
				$article_list[$key]['user_info'] = $article_users_info[$val['uid']];
			}
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/article/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		foreach ($headline as $key => $value) {
			echo $key.":".$value."___";
		}
		$temp = FORMAT::parse_attachs($headline['message'], TRUE);
		echo $temp;
		foreach ($temp as $key => $value) {
			echo "string".$key.":".$value;
		}
		$temp = $this->model('publish')->get_attach_by_id($temp['0']);
		echo $temp['attachment'];
		// foreach ($temp as $key => $value) {
		// 	echo "string".$key.":".$value;
		// }

		echo $headline['user_info']['signature'];

		TPL::assign('article_list', $article_list);
		TPL::assign('article_topics', $article_topics);

		TPL::assign('posts_list_bit', TPL::output('news/ajax/list', false));
		// echo "string";
		// echo $headline['title'];
		TPL::assign('headline', $headline);

		$this->crumb(AWS_APP::lang()->_t('媒体'), '/news/');
		TPL::output('news/square');
		// echo "string";
	}
}
?>