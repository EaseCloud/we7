<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';

class ArticleProcessor extends PluginProcessor
{
	public function __construct()
	{
		parent::__construct('article');
	}

	public function respond($obj = null)
	{
		global $_W;
		$message = $obj->message;
		$content = $obj->message['content'];
		$msgtype = strtolower($message['msgtype']);
		$event = strtolower($message['event']);
		if ($msgtype == 'text' || $event == 'click') {
			$page = pdo_fetch('select * from ' . tablename('sz_yi_article') . ' where article_keyword=:keyword and article_state=1 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':keyword' => $content));
			if (empty($page)) {
				return $this->responseEmpty();
			}
			$r_title = $page['article_title'];
			$r_desc = $page['resp_desc'];
			$r_img = $page['resp_img'];
			$r_img = set_medias($r_img);
			$news = array(array('title' => $r_title, 'picurl' => $r_img, 'description' => $r_desc, 'url' => $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=article&aid=' . $page['id'],));
			return $obj->respNews($news);
		}
		return $this->responseEmpty();
	}

	private function responseEmpty()
	{
		ob_clean();
		ob_start();
		echo '';
		ob_flush();
		ob_end_flush();
		exit(0);
	}
}
