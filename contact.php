<?php
/*
-------------------------------------------------------------------------
                     ADSLIGHT 2 : Module for Xoops

        Redesigned and ameliorate By Luc Bizet user at www.frxoops.org
        Started with the Classifieds module and made MANY changes
        Website : http://www.luc-bizet.fr
        Contact : adslight.translate@gmail.com
-------------------------------------------------------------------------
             Original credits below Version History
##########################################################################
#                    Classified Module for Xoops                         #
#  By John Mordo user jlm69 at www.xoops.org and www.jlmzone.com         #
#      Started with the MyAds module and made MANY changes               #
##########################################################################
 Original Author: Pascal Le Boustouller
 Author Website : pascal.e-xoops@perso-search.com
 Licence Type   : GPL
-------------------------------------------------------------------------
*/

use Xmf\Request;
use XoopsModules\Adslight;

require_once __DIR__ . '/header.php';

if (Request::hasVar('submit', 'POST')) {
    // Define Variables for register_globals Off. contribution by Peekay
    $id        = Request::getString('id', null);
    $date      = Request::getString('date', null);
    $namep     = Request::getString('namep', null);
    $ipnumber  = Request::getString('ipnumber', null);
    $messtext  = Request::getString('messtext', null);
    $typeprice = Request::getString('typeprice', null);
    $price     = Request::getString('price', null);
    $tele      = Request::getString('tele', null);
    // end define vars

    //    require_once __DIR__ . '/header.php';

    $module_id = $xoopsModule->getVar('mid');

    $module_id = $xoopsModule->getVar('mid');
    $groups    = ($xoopsUser instanceof \XoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    $perm_itemid = Request::getInt('item_id', 0, 'POST');

    //If no access
    if (!$grouppermHandler->checkRight('adslight_view', $perm_itemid, $groups, $module_id)) {
        redirect_header(XOOPS_URL . '/index.php', 3, _NOPERM);
    }
    global $xoopsConfig, $xoopsDB, $myts, $meta;

    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header(XOOPS_URL . '/modules/adslight/viewads.php?lid=' . addslashes($id) . '', 3, $GLOBALS['xoopsSecurity']->getErrors());
    }
    if ('1' == $GLOBALS['xoopsModuleConfig']['adslight_use_captcha']) {
        xoops_load('xoopscaptcha');
        $xoopsCaptcha = XoopsCaptcha::getInstance();
        if (!$xoopsCaptcha->verify()) {
            redirect_header(XOOPS_URL . '/modules/adslight/contact.php?lid=' . addslashes($id) . '', 2, $xoopsCaptcha->getMessage());
        }
    }
    $lid    = Request::getInt('id', 0, 'POST');
    $result = $xoopsDB->query('SELECT email, submitter, title, type, desctext, price, typeprice FROM  ' . $xoopsDB->prefix('adslight_listing') . ' WHERE lid = ' . $xoopsDB->escape($id));

    while (false !== (list($email, $submitter, $title, $type, $desctext, $price, $typeprice) = $xoopsDB->fetchRow($result))) {
        $teles = Request::getString('tele', '', 'POST');

        if ($price) {
            $price = '' . _ADSLIGHT_PRICE . ' ' . $GLOBALS['xoopsModuleConfig']['adslight_currency_symbol'] . " $price";
        } else {
            $price = '';
        }

        $date   = time();
        $r_usid = $GLOBALS['xoopsUser']->getVar('uid', 'E');

        $tags                = [];
        $tags['TITLE']       = $title;
        $tags['TYPE']        = Adslight\Utility::getNameType($type);
        $tags['PRICE']       = $price;
        $tags['DESCTEXT']    = stripslashes($desctext);
        $tags['MY_SITENAME'] = $xoopsConfig['sitename'];
        $tags['REPLY_ON']    = _ADSLIGHT_REMINDANN;
        $tags['DESCRIPT']    = _ADSLIGHT_DESC;
        $tags['STARTMESS']   = _ADSLIGHT_STARTMESS;
        $tags['MESSFROM']    = _ADSLIGHT_MESSFROM;
        $tags['CANJOINT']    = _ADSLIGHT_CANJOINT;
        $tags['NAMEP']       = Request::getString('namep', '', 'POST');
        $tags['TO']          = _ADSLIGHT_TO;
        $tags['POST']        = '<a href="mailto:' . Request::getString('post', '', 'POST') . '">' . Request::getString('post', '', 'POST') . '</a>';
        $tags['TELE']        = $teles;
        $tags['MESSAGE_END'] = _ADSLIGHT_MESSAGE_END;
        $tags['ENDMESS']     = _ADSLIGHT_ENDMESS;
        $tags['SECURE_SEND'] = _ADSLIGHT_SECURE_SEND;
        $tags['SUBMITTER']   = $submitter;
        $tags['MESSTEXT']    = stripslashes($messtext);
        $tags['EMAIL']       = _ADSLIGHT_EMAIL;
        $tags['TEL']         = _ADSLIGHT_TEL;
        $tags['HELLO']       = _ADSLIGHT_HELLO;
        $tags['REPLIED_BY']  = _ADSLIGHT_REPLIED_BY;
        $tags['YOUR_AD']     = _ADSLIGHT_YOUR_AD;
        $tags['THANKS']      = _ADSLIGHT_THANKS;
        $tags['WEBMASTER']   = _ADSLIGHT_WEBMASTER;
        $tags['SITE_URL']    = '<a href="' . XOOPS_URL . '">' . XOOPS_URL . '</a>';
        $tags['AT']          = _ADSLIGHT_AT;
        $tags['LINK_URL']    = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewads.php?lid=' . addslashes($id) . '">' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewads.php?lid=' . addslashes($id) . '</a>';
        $tags['VIEW_AD']     = _ADSLIGHT_VIEW_AD;

        $subject = '' . _ADSLIGHT_CONTACTAFTERANN . '';
        $mail    = xoops_getMailer();

        $mail->setTemplateDir(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/mail_template/');
        $mail->setTemplate('listing_contact.tpl');

        $mail->useMail();
        $mail->setFromEmail(Request::getString('post', '', 'POST'));
        $mail->setToEmails($email);
        $mail->setSubject($subject);
        $mail->multimailer->isHTML(true);
        $mail->assign($tags);
        //  $mail->setBody(stripslashes("$message"));
        $mail->send();
        echo $mail->getErrors();

        $xoopsDB->query('INSERT INTO ' . $xoopsDB->prefix('adslight_ip_log') . " values ( '', '$lid', '$date', '$namep', '$ipnumber', '" . Request::getString('post', '', 'POST') . "')");

        $xoopsDB->query('INSERT INTO ' . $xoopsDB->prefix('adslight_replies') . " values ('','$id', '$title', '$date', '$namep', '$messtext', '$tele', '" . Request::getString('post', '', 'POST') . "', '$r_usid')");

        redirect_header('index.php', 3, _ADSLIGHT_MESSEND);
    }
} else {
    $lid = Request::getInt('lid', 0, 'GET');
    $idd = $idde = $iddee = '';
    require_once __DIR__ . '/header.php';

    global $xoopsConfig, $xoopsDB, $myts, $meta;

    $module_id = $xoopsModule->getVar('mid');
    if (is_object($GLOBALS['xoopsUser'])) {
        $groups = $GLOBALS['xoopsUser']->getGroups();
    } else {
        $groups = XOOPS_GROUP_ANONYMOUS;
    }
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');
    $perm_itemid      = Request::getInt('item_id', 0, 'POST');
    //If no access
    if (!$grouppermHandler->checkRight('adslight_view', $perm_itemid, $groups, $module_id)) {
        redirect_header(XOOPS_URL . '/index.php', 3, _NOPERM);
    }

    //    require_once XOOPS_ROOT_PATH . '/modules/adslight/include/gtickets.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    require_once XOOPS_ROOT_PATH . '/header.php';
    echo "<table width='100%' border='0' cellspacing='1' cellpadding='8'><tr class='bg4'><td valign='top'>\n";
    $time     = time();
    $ipnumber = (string)$_SERVER[REMOTE_ADDR];
    echo '<script type="text/javascript">
          function verify()
          {
                var msg = "' . _ADSLIGHT_VALIDERORMSG . '\\n__________________________________________________\\n\\n";
                var errors = "FALSE";
                if (window.document.cont.namep.value == "") {
                        errors = "TRUE";
                        msg += "' . _ADSLIGHT_VALIDSUBMITTER . '\\n";
                }
                if (window.document.cont.post.value == "") {
                        errors = "TRUE";
                        msg += "' . _ADSLIGHT_VALIDEMAIL . '\\n";
                }
                if (window.document.cont.messtext.value == "") {
                        errors = "TRUE";
                        msg += "' . _ADSLIGHT_VALIDMESS . '\\n";
                }
                if (errors == "TRUE") {
                        msg += "__________________________________________________\\n\\n' . _ADSLIGHT_VALIDMSG . '\\n";
                        alert(msg);

                        return false;
                }
          }
          </script>';

    echo '<b>' . _ADSLIGHT_CONTACTAUTOR . '</b><br><br>';
    echo '' . _ADSLIGHT_TEXTAUTO . '<br>';
    echo '<form onSubmit="return verify();" method="post" action="contact.php" name="cont">';
    echo "<input type=\"hidden\" name=\"id\" value=\"$lid\" >";
    echo '<input type="hidden" name="submit" value="1" >';
    echo "<table width='100%' class='outer' cellspacing='1'>
    <tr>
      <td class='head'>" . _ADSLIGHT_YOURNAME . '</td>';
    if ($GLOBALS['xoopsUser']) {
        $idd  = $GLOBALS['xoopsUser']->getVar('uname', 'E');
        $idde = $GLOBALS['xoopsUser']->getVar('email', 'E');

        echo "<td class='even'><input type=\"text\" name=\"namep\" size=\"42\" value=\"$idd\" >";
    } else {
        echo "<td class='even'><input type=\"text\" name=\"namep\" size=\"42\" ></td>";
    }
    echo "</tr>
    <tr>
      <td class='head'>" . _ADSLIGHT_YOUREMAIL . "</td>
      <td class='even'><input type=\"text\" name=\"post\" size=\"42\" value=\"$idde\" ></font></td>
    </tr>
    <tr>
      <td class='head'>" . _ADSLIGHT_YOURPHONE . "</td>
      <td class='even'><input type=\"text\" name=\"tele\" size=\"42\" ></font></td>
    </tr>
    <tr>
      <td class='head'>" . _ADSLIGHT_YOURMESSAGE . "</td>
      <td class='even'><textarea rows=\"5\" name=\"messtext\" cols=\"40\" ></textarea></td>
    </tr>";
    if ('1' == $GLOBALS['xoopsModuleConfig']['adslight_use_captcha']) {
        echo "<tr><td class='head'>" . _ADSLIGHT_CAPTCHA . " </td><td class='even'>";
        $jlm_captcha = '';
        $jlm_captcha = new \XoopsFormCaptcha(_ADSLIGHT_CAPTCHA, 'xoopscaptcha', false);
        echo $jlm_captcha->render();
    }

    echo '</td></tr></table>';
    echo "<table class='outer'><tr><td>" . _ADSLIGHT_YOUR_IP . '&nbsp;
        <img src="' . XOOPS_URL . '/modules/adslight/ip_image.php" alt="" ><br>' . _ADSLIGHT_IP_LOGGED . '
        </td></tr></table>
    <br>';
    echo '<input type="hidden" name="ip_id" value="" >';
    echo "<input type=\"hidden\" name=\"lid\" value=\"$lid\" >";
    echo "<input type=\"hidden\" name=\"ipnumber\" value=\"$ipnumber\" >";
    echo "<input type=\"hidden\" name=\"date\" value=\"$time\" >";
    echo '<p><input type="submit" name="submit" value="' . _ADSLIGHT_SENDFR . '" ></p>
' . $GLOBALS['xoopsSecurity']->getTokenHTML() . '
    </form>';
}
echo '</td></tr></table>';
require_once XOOPS_ROOT_PATH . '/footer.php';
