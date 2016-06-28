<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('SystemModel')) {
    class SystemModel extends PluginModel
    {
        public function get_wechats()
        {
            return pdo_fetchall("SELECT  a.uniacid,a.name FROM " . tablename('account_wechats') . " a  " . " left join " . tablename('sz_yi_sysset') . " s on a.uniacid = s.uniacid");
        }
        public function getCopyright()
        {
            global $_W;
            $copyrights = m('cache')->getArray('systemcopyright', 'global');
            if (!is_array($copyrights)) {
                $copyrights = pdo_fetchall('select *  from ' . tablename('sz_yi_system_copyright'), array(), 'uniacid');
                m('cache')->set('systemcopyright', $copyrights, 'global');
            }
            $copyright = false;
            if (isset($copyrights[$_W['uniacid']])) {
                $copyright = $copyrights[$_W['uniacid']];
            } else if (isset($copyrights[-1])) {
                $copyright = $copyrights[-1];
            }
            return $copyright;
        }
    }
}
