<?php

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER
{
	public $per_page;

	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

		$rule_action['actions'] = array(
			'news_actions'
		);

		return $rule_action;
	}


	public function news_actions_action()
	{
		// echo $_GET['page']."qwertyuiofayhfihaeluhfliahflhalfhwiuahflahfniuwahflnh";
		// echo "page:".$_GET['page'];
		// echo "category:".$category_info['id'];
		// echo "per_page:".get_setting('contents_per_page');
		$article_list = $this->model('article')->get_articles_list($category_info['id'], intval($_GET['page']), get_setting('contents_per_page'), 'recommend_time DESC');
		// echo "  list size:".sizeof($article_list);

		if ($article_list)
		{
			foreach ($article_list as $key => $value) 
			{
				if ($value['is_recommend'] != '2' OR $value['is_headline'] == '1')
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

		TPL::assign('article_list', $article_list);
		TPL::assign('article_topics', $article_topics);

		// if (is_mobile())
		// {
		// 	TPL::output('m/ajax/index_actions');
		// }
		// else
		// {
			TPL::output('news/ajax/list');
		// }		
	}
}
?>