<?php

/**
 * Created by IntelliJ IDEA.
 * User: codeMonkey QQ:631872807
 * Date: 2015/1/18
 * Time: 16:50
 */
class Oauth2
{


    public static $SCOPE_BASE = "snsapi_base";
    public static $SCOPE_USERINFO = "snsapi_userinfo";

    private $appid = "";
    private $secret = "";

    function __construct($appid, $secret)
    {
        $this->appid = $appid;
        $this->secret = $secret;
    }

    /**
     * author: codeMonkey QQ:631872807
     * @param $redirect_uri
     * @param $scope
     * @param $state
     */
    public function authorization_code($redirect_uri, $scope, $state)
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->appid . "&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";
        header("location: $url");

    }


    /**
     * author: codeMonkey QQ:631872807
     * @param $code
     * @return mixed
     * {
     * "access_token":"ACCESS_TOKEN",
     * "expires_in":7200,
     * "refresh_token":"REFRESH_TOKEN",
     * "openid":"OPENID",
     * "scope":"SCOPE"

     */
    public function  getOauthAccessToke($code)
    {

        $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->secret . "&code=" . $code . "&grant_type=authorization_code";
        $content = ihttp_get($oauth2_code);
        $token = @json_decode($content['content'], true);


        if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {

            echo '<h1>获取微信公众号授权' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: <br />' . $content['meta'] . '<h1>';
            exit();
        }

        return $token;

    }


    /**
     * 获取用户信息
     *
     * @param unknown $openid
     * @param unknown $accessToken
     * @return unknown
     */
    public function getOauthUserInfo($openid, $accessToken)
    {


        $tokenUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $accessToken . "&openid=" . $openid . "&lang=zh_CN";
        $content = ihttp_get($tokenUrl);

        $userInfo = @json_decode($content['content'], true);

        return $userInfo;
    }


    /**
     * author: codeMonkey QQ:631872807
     * 全局access_token获取
     * @return
     */
    public function getAccessToken()
    {
        global $_W;


        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $accessToken = CRUD::findUnique(CRUD::$table_sin_token, array(":weid" => $_W['weid']));

		load()->func('communication');
        if (!empty($accessToken)) {

            $expires_in = $accessToken['expires_in'];

            if (TIMESTAMP - $accessToken['createtime'] >= $expires_in - 200) { // 过期

                $content = ihttp_get($tokenUrl);
                $token = @json_decode($content['content'], true);
                $data = array(
                    'weid' => $_W['weid'],
                    'access_token' => $token['access_token'],
                    'expires_in' => $token['expires_in'],
                    'createtime' => TIMESTAMP
                );

                CRUD::updateById(CRUD::$table_sin_token, $data, $accessToken['id']);

                return $token['access_token'];
            } else {

                return $accessToken['access_token'];
            }
        } else {

            $content = ihttp_get($tokenUrl);
            $token = @json_decode($content['content'], true);
            $data = array(
                'weid' => $_W['weid'],
                'access_token' => $token['access_token'],
                'expires_in' => $token['expires_in'],
                'createtime' => TIMESTAMP
            );

            CRUD::create(CRUD::$table_sin_token, $data);


            return $token['access_token'];
        }
    }


    public function  getUserInfo($access_token, $openid)
    {


        $api_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";

        $content = ihttp_get($api_url);

        $userInfo = @json_decode($content['content'], true);

        return $userInfo;

    }


}