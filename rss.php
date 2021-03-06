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

use XoopsModules\Adslight;

header('Content-Type: application/rss+xml; charset=UTF-8');

require_once __DIR__ . '/header.php';
//require_once __DIR__ . '/include/functions.php';
$allads     = [];
$allads     = Adslight\Utility::returnAllAdsRss();
$base_xoops = 'http://' . $_SERVER['SERVER_NAME'] . mb_substr($_SERVER['REQUEST_URI'], 0, mb_strpos($_SERVER['REQUEST_URI'], 'modules'));

echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>' . $xoopsConfig['sitename'] . '</title>
<link>' . $base_xoops . '</link>
<description>' . $xoopsConfig['slogan'] . '</description>
<language>fr</language>';

$adslink = 'http://' . $_SERVER['SERVER_NAME'] . mb_substr($_SERVER['REQUEST_URI'], 0, mb_strrpos($_SERVER['REQUEST_URI'], '/'));

for ($i = 0, $iMax = count($allads); $i < $iMax; ++$i) {
    echo '<item>
<title>' . $allads[$i]['title'] . '</title>
<description><![CDATA[' . stripslashes($allads[$i]['desctext']) . '<br><strong>Ville:</strong> ' . htmlspecialchars($allads[$i]['town'], ENT_QUOTES | ENT_HTML5) . ' - <strong>Prix:</strong> ' . htmlspecialchars($allads[$i]['price'], ENT_QUOTES | ENT_HTML5) . '&#8364; <br>';
    echo ']]></description>
<link><![CDATA[' . $adslink . '/viewads.php?lid=' . $allads[$i]['lid'] . ']]></link>
<guid><![CDATA[' . $adslink . '/viewads.php?lid=' . $allads[$i]['lid'] . ']]></guid>
<pubDate>' . date('D, d M Y H:i:s +0100', $allads[$i]['date']) . '</pubDate>
</item>';
}
echo '</channel>
</rss>';
