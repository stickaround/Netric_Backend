<?php
    require_once("src/AntLegacy/aereus.lib.php/CCache.php");

    // get the antsystem account id (external to local database)
    /*
    function settingsGetSysAccountId($sys_account)
    {
        global $settings_db_syshost, $settings_db_sysdb;

        $ret = null;

        if ($settings_db_syshost && $settings_db_sysdb)
        {
            $cache = CCache::getInstance();
            $cval = $cache->get($settings_db_sysdb."/accounts/$sys_account/id");
            if (!$cval)
            {
                // Get database to use from account
                $dbh_sys = new CDatabase($settings_db_syshost, $settings_db_sysdb);
                $result = $dbh_sys->Query("select id from accounts where name='$sys_account'");
                if ($dbh_sys->GetNumberRows($result))
                {
                    $ret = $dbh_sys->GetValue($result, 0, "id");
                    $cache->set($settings_db_sysdb."/accounts/$sys_account/id", $ret);
                }
            }
            else
            {
                $ret = $cval;
            }
        }

        return $ret;
    }
     */

    /*
    function settingsGetSysAccountNameFromId($aid)
    {
        global $settings_db_syshost, $settings_db_sysdb, $settings_db_sysuser, $settings_db_syspass, $settings_db_type;

        $ret = null;

        if ($settings_db_syshost && $settings_db_sysdb && $settings_db_sysuser && $settings_db_syspass && $settings_db_type)
        {
            $cache = CCache::getInstance();
            $cval = $cache->get($settings_db_sysdb."/accounts/$aid/name");
            if ($cval === false)
            {
                // Get database to use from account
                $dbh_sys = new CDatabase($settings_db_syshost, $settings_db_sysdb);
                $result = $dbh_sys->Query("select name from accounts where id='$aid'");
                if ($dbh_sys->GetNumberRows($result))
                {
                    $ret = $dbh_sys->GetValue($result, 0, "name");
                    $cache->set($settings_db_sysdb."/accounts/$aid/name", $ret);
                }
            }
            else
            {
                $ret = $cval;
            }
        }

        return $ret;
    }
     */

function settingsGetAccountName()
{
    global $_SERVER, $_GET, $_POST, $settings_localhost_root, $settings_localhost, $ANT;

    $ret = null;

    // Check ANT
    if ($ANT) {
        $ret = $ANT->accountName;
    }

    // 1 check session
    $ret = Ant::getSessionVar('aname');

    // 2 check url - 3rd level domain is the account name
    if (!$ret && $settings_localhost!=$settings_localhost_root && strpos($settings_localhost, ".$settings_localhost_root")) {
        $left = str_replace(".$settings_localhost_root", '', $settings_localhost);
        if ($left) {
            $ret = $left;
        }
    }
        
    // 3 check get
    if (!$ret && isset($_GET['account'])) {
        $ret = $_GET['account'];
    }

    // 4 check post
    if (!$ret && isset($_POST['account'])) {
        $ret = $_POST['account'];
    }

    // 5 get default (top)
    if (!$ret) {
        $ret = AntConfig::getInstance()->default_account;
    }

    return $ret;
}

function settingsAccountSet($dbh, $account, $name, $val)
{
    $result = $dbh->Query("select id from settings where name='$name' and user_id is null");
    if ($dbh->GetNumberRows($result)) {
        $row = $dbh->GetNextRow($result, 0);
        $dbh->Query("update settings set value='".$dbh->Escape($val)."' where id='".$row['id']."'");
    } else {
        $dbh->Query("insert into settings(name, value) values('$name', '".$dbh->Escape($val)."');");
    }

    return true;
}
