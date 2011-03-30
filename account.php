<?php
/**
 * copyright 2009 Lucas Baudin <xapantu@gmail.com>
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
<?php
/***************************************************************************
Project: STK Addon Manager

File: manageAccount.php
Version: 1
Licence: GPLv3
Description: people

***************************************************************************/
$security = 'basicPage';
define('ROOT','./');
include('include.php');

$_GET['title'] = (isset($_GET['title'])) ? $_GET['title'] : NULL;

$title = _('SuperTuxKart Add-ons').' | '._('Users');
include('include/top.php');
?>
</head>
<body>
<?php 
include(ROOT.'include/menu.php');
?>
<div id="select-addons">
<div id="select-addons_top">
</div>
<div id="select-addons_center">
<?php
$js = "";
loadUsers();
?>
</div>
<div id="select-addons_bottom">
</div></div>
<div id="content-addon">
    <div id="content-addon_top"></div>
    <div id="content-addon_body"></div>
    <div id="content-addon_bottom"></div>
</div>
<?php
echo '<script type="text/javascript">';
echo $js;
echo '</script>';
include("include/footer.php"); ?>
</body>
</html>
