<?php
function attachment_alioss_buctkets($key, $secret) {
	require_once('../framework/library/alioss/sdk.class.php');
	$oss = new ALIOSS($key, $secret);
	$response = $oss->list_bucket();
	if (!empty($response)) {
		$xml = isimplexml_load_string($response->body, 'SimpleXMLElement', LIBXML_NOCDATA);
		$buckets = json_decode(json_encode($xml), true);
	}
	if (empty($buckets['Buckets'])) {
		return error(-1, $buckets['Message']);
	}
	if (empty($buckets['Buckets']['Bucket'][0])) {
		$buckets['Buckets']['Bucket'] = array($buckets['Buckets']['Bucket']);
	}
	$bucket_container = array();
	if (!empty($buckets['Buckets']['Bucket'])) {
		foreach ($buckets['Buckets']['Bucket'] as $bucket) {
			$bucket_container[$bucket['Name']] = array('name' => $bucket['Name'], 'location' => $bucket['Location']);
		}
	}
	return $bucket_container;
}

function attachment_qiniu_auth($key, $secret,$bucket) {
	require_once(IA_ROOT . '/framework/library/qiniu/autoload.php');
	$auth = new Qiniu\Auth($key, $secret);
	$token = $auth->uploadToken($bucket);
	$uploadmgr = new Qiniu\Storage\UploadManager();
	list($ret, $err) = $uploadmgr->putFile($token, 'MicroEngine.ico', ATTACHMENT_ROOT.'images/global/MicroEngine.ico');
	if ($err !== null) {
		$err = (array)$err;
		$err = (array)array_pop($err);
		$err = json_decode($err['body'], true);
		return error(-1, $err);
	} else {
		return true;
	}
}
