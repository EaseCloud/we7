<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

define('REGULAR_EMAIL', '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i');
define('REGULAR_MOBILE', '/1\d{10}/');
define('REGULAR_USERNAME', '/^[\x{4e00}-\x{9fa5}a-z\d_\.]{3,15}$/iu');

define('TEMPLATE_DISPLAY', 0);
define('TEMPLATE_FETCH', 1);
define('TEMPLATE_INCLUDEPATH', 2);

define('ACCOUNT_SUBSCRIPTION', 1);
define('ACCOUNT_SUBSCRIPTION_VERIFY', 3);
define('ACCOUNT_SERVICE', 2);
define('ACCOUNT_SERVICE_VERIFY', 4);
define('ACCOUNT_OAUTH_LOGIN', 3);
define('ACCOUNT_NORMAL_LOGIN', 1);

define('WEIXIN_ROOT', 'https://mp.weixin.qq.com');

define('ACCOUNT_OPERATE_ONLINE', 1);
define('ACCOUNT_OPERATE_MANAGER', 2);
define('ACCOUNT_OPERATE_CLERK', 3);
