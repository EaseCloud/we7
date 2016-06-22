<?php 
$url = 'http://toutiao.com/api/article/recent/?source=2&category=news_hot&max_behot_time=0&_='.TIMESTAMP;
$resp = ihttp_get($url);
if ($resp['code'] == 200 && $resp['content']) {
	$obj= json_decode($resp['content'], true);
	$news[] = array('title' => '今日头条', 'description' => '头条新闻', 'url' => 'http://toutiao.com/', 'picurl' => 'http://a.hiphotos.baidu.com/baike/w%3D268/sign=db923e4310ce36d3a204843602f23a24/7dd98d1001e93901a1184fa77eec54e736d19615.jpg');
	$cnt = min(count($obj['data']), 8);
	for($i = 0; $i < $cnt; $i++) {
		$news[] = array(
			'title' => strval($obj['data'][$i]['title']),
			'description' => strval($obj['data'][$i]['abstract']),
			'picurl' => '',
			'url' => strval($obj['data'][$i]['article_url'])
		);
	}
	return $this->respNews($news);

}
return $this->respText('没有找到结果, 要不过一会再试试?');
