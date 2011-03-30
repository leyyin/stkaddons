<?php
/**
 * copyright 2011 Stephen Just <stephenjust@users.sf.net>
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

define('ROOT','./');
include_once('config.php');
include_once('include/sql.php');

$dir = $_GET['type'];
$file = $_GET['file'];
// Make sure directory is not unsafe
if (!preg_match('/^[a-z]+$/i',$dir))
{
    // Directory is unsafe - throw a 404 error
    header("HTTP/1.0 404 Not Found");
    exit;
}
// Make sure file name is not unsafe
if (!preg_match('/^[a-z0-9\-_\ ]+\.[a-z0-9]+$/i',$file))
{
    // File is unsafe - throw a 404 error
    header("HTTP/1.0 404 Not Found");
    exit;
}

if ($dir != 'assets')
    $filepath = UP_LOCATION.$dir.'/'.$file;
else
    $filepath = UP_LOCATION.$file;

if (!file_exists($filepath))
{
    // File does not exist
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Check user-agent
$uagent = $_SERVER['HTTP_USER_AGENT'];
if (preg_match('#^SuperTuxKart/[a-z0-9\.\-_]+$#',$uagent)) {
    // Check if this user-agent is already known
    $checkSql = 'SELECT * FROM `'.DB_PREFIX.'clients`
        WHERE `agent_string` = \''.mysql_real_escape_string($uagent).'\'';
    $checkHandle = sql_query($checkSql);
    if (mysql_num_rows($checkHandle) != 1)
    {
        // New user-agent. Add it to the database.
        $newSql = 'INSERT INTO `'.DB_PREFIX.'clients`
            (`agent_string`) VALUES (\''.mysql_real_escape_string($uagent).'\')';
        $newHandle = sql_query($newSql);
    }
    else
    {
        $checkResult = sql_next($checkHandle);
        if ($checkResult['disabled'] == 1)
        {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }
}

// File exists
// Send headers
$filesize = filesize($filepath);

// Determine content type
$path_parts = pathinfo($filepath);
$ext = strtolower($path_parts['extension']);
switch ($ext) {
    case "xml": $ctype="application/xml"; break;
    case "dtd": $ctype="application/xml-dtd"; break;
    case "zip": $ctype="application/zip"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpg"; break;
    default: $ctype="application/force-download";
}
$mtime = filemtime($filepath);
$mtimestring = gmdate('D, d M Y H:i:s',$mtime);

header("Pragma: public");
header("Content-Type: $ctype");
header("Content-Length: $filesize");
header("Last-Modified: $mtimestring GMT");

// Send file
readfile($filepath);
?>