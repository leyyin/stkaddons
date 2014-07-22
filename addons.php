<?php
/**
 * Copyright 2009      Lucas Baudin <xapantu@gmail.com>
 *           2011-2014 Stephen Just <stephenjust@gmail.com>
 *           2014      Daniel Butum <danibutum at gmail dot com>
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
require_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");

// Validate addon-id parameter
$_GET['type'] = (isset($_GET['type'])) ? $_GET['type'] : null;
$_GET['name'] = (isset($_GET['name'])) ? Addon::cleanId($_GET['name']) : null; // name is actually the id
$_GET['save'] = (isset($_GET['save'])) ? $_GET['save'] : null;
$_GET['rev'] = (isset($_GET['rev'])) ? (int)$_GET['rev'] : null;
$addon_exists = Addon::exists($_GET["name"]);

// addon type is optional
if (is_null($_GET['type']))
{
    $_GET['type'] = Addon::getTypeByID($_GET['name']);
}

// check type
switch ($_GET['type'])
{
    case Addon::TRACK:
        $type_label = _h('Tracks');
        break;

    case Addon::KART:
        $type_label = _h('Karts');
        break;

    case Addon::ARENA:
        $type_label = _h('Arenas');
        break;

    default:
        $type_label = _h('Unknown Type');
        $panel["status"] = '<span class="error">' . _h('Invalid addon type.') . '</span><br />';
        $tpl->assign("panel", $panel);
        exit($tpl);
        break;
}

// build title
$title = $type_label . ' - ' . _h('SuperTuxKart Add-ons');
$addonName = Addon::getNameByID($_GET['name']);
if ($addonName)
{
    $title = $addonName . ' - ' . $title;
}

// build template
$tpl = StkTemplate::get("two-pane.tpl")
    ->assign("title", $title)
    ->addUtilLibrary()
    ->addScriptInclude("addon.js");
$panel = [
    'left'   => '',
    'status' => '',
    'right'  => ''
];

// Execute actions
$status = "";
try
{
    switch ($_GET['save'])
    {
        case 'props':
            if (!isset($_POST['description']))
            {
                break;
            }
            if (!isset($_POST['designer']))
            {
                break;
            }

            $edit_addon = new Addon($_GET['name']);
            $edit_addon->setDescription($_POST['description']);
            $edit_addon->setDesigner($_POST['designer']);
            $status = _h('Saved properties.') . '<br>';
            break;

        case 'rev':
            parseUpload($_FILES['file_addon'], true);
            break;

        case 'status':
            if (!isset($_GET['name']) || !isset($_POST['fields']))
            {
                break;
            }
            $addon = new Addon($_GET['name']);
            $addon->setStatus($_POST['fields']);
            $status = _h('Saved status.') . '<br>';
            break;

        case 'notes':
            if (!isset($_GET['name']) || !isset($_POST['fields']))
            {
                break;
            }

            $mAddon = new Addon($_GET['name']);
            $mAddon->setNotes($_POST['fields']);
            $status = _h('Saved notes.') . '<br>';
            break;

        case 'delete':
            $delAddon = new Addon($_GET['name']);
            $delAddon->delete();
            $status = _h('Deleted addon.') . '<br>';
            break;

        case 'del_rev':
            $delRev = new Addon($_GET['name']);
            $delRev->deleteRevision($_GET['rev']);
            $status = _h('Deleted add-on revision.') . '<br>';
            break;

        case 'approve':
        case 'unapprove':
            $approve = ($_GET['save'] === 'approve') ? true : false;
            File::approve((int)$_GET['id'], $approve);
            $status = _h('File updated.') . '<br>';
            break;

        case 'setimage':
            $edit_addon = new Addon($_GET['name']);
            $edit_addon->setImage((int)$_GET['id']);
            $status = _h('Set image.') . '<br>';
            break;

        case 'seticon':
            if ($_GET['type'] !== 'karts')
            {
                break;
            }
            $edit_addon = new Addon($_GET['name']);
            $edit_addon->setImage((int)$_GET['id'], 'icon');
            $status = _h('Set icon.') . '<br>';
            break;

        case 'deletefile':
            $mAddon = new Addon($_GET['name']);
            $mAddon->deleteFile((int)$_GET['id']);
            $status = _h('Deleted file.') . '<br>';
            break;

        case 'include':
            $mAddon = new Addon($_GET['name']);
            $mAddon->setIncludeVersions($_POST['incl_start'], $_POST['incl_end']);
            $status = _h('Marked game versions in which this add-on is included.');
            break;

        default:
            $status = _h('Addon action is not recognized');
            break;
    }
}
catch(Exception $e)
{
    $status = '<span class="error">' . $e->getMessage() . '</span><br />';
}
$panel["status"] = $status;

$addons = [];
$addons_list = Addon::getAddonList($_GET['type'], true);
foreach ($addons_list as $ad)
{
    try
    {
        $adc = new Addon($ad);

        // Get link icon
        if ($adc->getType() === Addon::KART)
        {
            // Make sure an icon file is set for kart
            if ($adc->getImage(true) != 0)
            {
                $im = Cache::getImage($adc->getImage(true), ['size' => 'small']);
                if ($im['exists'] && $im['approved'])
                {
                    $icon = $im['url'];
                }
                else
                {
                    $icon = IMG_LOCATION . 'kart-icon.png';
                }
            }
            else
            {
                $icon = IMG_LOCATION . 'kart-icon.png';
            }
        }
        else
        {
            $icon = IMG_LOCATION . 'track-icon.png';
        }

        // Approved?
        if ($adc->hasApprovedRevision())
        {
            $class = 'addon-list menu-item';
        }
        elseif (User::hasPermission(AccessControl::PERM_EDIT_ADDONS) || User::getLoggedId() == $adc->getUploaderId())
        {
            // not approved, see of we are logged in and we have permission
            $class = 'addon-list menu-item unavailable';
        }
        else
        {
            // do not show
            continue;
        }

        $icon_html = '<img class="icon" src="' . $icon . '" height="25" width="25" />';
        if (($adc->getStatus() & F_FEATURED) == F_FEATURED)
        {
            $icon_html = '<div class="icon-featured"></div>' . $icon_html;
        }
        $addons[] = [
            'class' => $class,
            'url'   => "addons.php?type={$_GET['type']}&amp;name={$adc->getId()}",
            'label' => '<div class="icon">' . $icon_html . '</div>' . h($adc->getName()),
            'disp'  => File::rewrite("addons.php?type={$_GET['type']}&amp;name={$adc->getId()}")
        ];
    }
    catch(AddonException $e)
    {
        $panel["status"] = '<span class="error">' . $e->getMessage() . '</span><br />';
    }
}

// left panel
$left_tpl = new StkTemplate('url-list-panel.tpl');
$left_tpl->assign('items', $addons);
$panel['left'] = (string)$left_tpl;

// right panel
if ($addon_exists)
{
    $panel['right'] = Util::ob_get_require_once(ROOT_PATH . 'addons-panel.php');
}
else if (!is_null($_GET["name"]))
{
    $panel['right'] = _h("The addon name does not exist");
}

// output the view
$tpl->assign('panel', $panel);
echo $tpl;

