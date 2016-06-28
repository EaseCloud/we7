<?php
require_once('http.php');
require_once('auth_digest.php');
class Qiniu_Rio_PutExtra
{
    public $Bucket = null;
    public $Params = null;
    public $MimeType = null;
    public $ChunkSize = 0;
    public $TryTimes = 3;
    public $Progresses = null;
    public $Notify = null;
    public $NotifyErr = null;
    public function __construct($bucket = null)
    {
        $this->Bucket = $bucket;
    }
}
define('QINIU_RIO_BLOCK_BITS', 22);
define('QINIU_RIO_BLOCK_SIZE', 1 << QINIU_RIO_BLOCK_BITS);
function Qiniu_Rio_BlockCount($fsize)
{
    return ($fsize + (QINIU_RIO_BLOCK_SIZE - 1)) >> QINIU_RIO_BLOCK_BITS;
}
function Qiniu_Rio_Mkblock($self, $host, $reader, $size)
{
    if (is_resource($reader)) {
        $body = fread($reader, $size);
        if ($body === false) {
            $err = new Qiniu_Error(0, 'fread failed');
            return array(
                null,
                $err
            );
        }
    } else {
        list($body, $err) = $reader->Read($size);
        if ($err !== null) {
            return array(
                null,
                $err
            );
        }
    }
    if (strlen($body) != $size) {
        $err = new Qiniu_Error(0, 'fread failed: unexpected eof');
        return array(
            null,
            $err
        );
    }
    $url = $host . '/mkblk/' . $size;
    return Qiniu_Client_CallWithForm($self, $url, $body, 'application/octet-stream');
}
function Qiniu_Rio_Mkfile($self, $host, $key, $fsize, $extra)
{
    $url = $host . '/mkfile/' . $fsize;
    if ($key !== null) {
        $url .= '/key/' . Qiniu_Encode($key);
    }
    if (!empty($extra->MimeType)) {
        $url .= '/mimeType/' . Qiniu_Encode($extra->MimeType);
    }
    if (!empty($extra->Params)) {
        foreach ($extra->Params as $k => $v) {
            $url .= "/" . $k . "/" . Qiniu_Encode($v);
        }
    }
    $ctxs = array();
    foreach ($extra->Progresses as $prog) {
        $ctxs[] = $prog['ctx'];
    }
    $body = implode(',', $ctxs);
    return Qiniu_Client_CallWithForm($self, $url, $body, 'application/octet-stream');
}
class Qiniu_Rio_UploadClient
{
    public $uptoken;
    public function __construct($uptoken)
    {
        $this->uptoken = $uptoken;
    }
    public function RoundTrip($req)
    {
        $token                        = $this->uptoken;
        $req->Header['Authorization'] = "UpToken $token";
        return Qiniu_Client_do($req);
    }
}
function Qiniu_Rio_Put($upToken, $key, $body, $fsize, $putExtra)
{
    global $QINIU_UP_HOST;
    $self       = new Qiniu_Rio_UploadClient($upToken);
    $progresses = array();
    $uploaded   = 0;
    while ($uploaded < $fsize) {
        $tried     = 0;
        $tryTimes  = ($putExtra->TryTimes > 0) ? $putExtra->TryTimes : 1;
        $blkputRet = null;
        $err       = null;
        if ($fsize < $uploaded + QINIU_RIO_BLOCK_SIZE) {
            $bsize = $fsize - $uploaded;
        } else {
            $bsize = QINIU_RIO_BLOCK_SIZE;
        }
        while ($tried < $tryTimes) {
            list($blkputRet, $err) = Qiniu_Rio_Mkblock($self, $QINIU_UP_HOST, $body, $bsize);
            if ($err === null) {
                break;
            }
            $tried += 1;
            continue;
        }
        if ($err !== null) {
            return array(
                null,
                $err
            );
        }
        if ($blkputRet === null) {
            $err = new Qiniu_Error(0, "rio: uploaded without ret");
            return array(
                null,
                $err
            );
        }
        $uploaded += $bsize;
        $progresses[] = $blkputRet;
    }
    $putExtra->Progresses = $progresses;
    return Qiniu_Rio_Mkfile($self, $QINIU_UP_HOST, $key, $fsize, $putExtra);
}
function Qiniu_Rio_PutFile($upToken, $key, $localFile, $putExtra)
{
    $fp = fopen($localFile, 'rb');
    if ($fp === false) {
        $err = new Qiniu_Error(0, 'fopen failed');
        return array(
            null,
            $err
        );
    }
    $fi     = fstat($fp);
    $result = Qiniu_Rio_Put($upToken, $key, $fp, $fi['size'], $putExtra);
    fclose($fp);
    return $result;
}
