<?php
function clean_path($path)
{
    $path = trim($path);
    $path = trim($path, '\\/');
    $path = str_replace(array('../', '..\\'), '', $path);
    if ($path == '..') {
        $path = '';
    }
    return str_replace('\\', '/', $path);
}

function get_parent_path($path)
{
    if ($path != '') {
        $array = explode('/', $path);
        if (count($array) > 1) {
            $array = array_slice($array, 0, -1);
            return implode('/', $array);
        }
        return '';
    }
    return false;
}

function redirect($url, $code = 302)
{
    header('Location: ' . $url, true, $code);
    exit;
}

function is_utf8($string)
{
    return preg_match('//u', $string);
}

function get_mime_type($file_path)
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        return $mime;
    } elseif (function_exists('mime_content_type')) {
        return mime_content_type($file_path);
    } elseif (!stristr(ini_get('disable_functions'), 'shell_exec')) {
        $file = escapeshellarg($file_path);
        $mime = shell_exec('file -bi ' . $file);
        return $mime;
    } else {
        return '--';
    }
}

function get_zip_info($path)
{
    if (function_exists('zip_open')) {
        $arch = zip_open($path);
        if ($arch) {
            $filenames = array();
            while ($zip_entry = zip_read($arch)) {
                $zip_name = zip_entry_name($zip_entry);
                $zip_folder = substr($zip_name, -1) == '/';
                $filenames[] = array(
                    'name' => $zip_name,
                    'filesize' => zip_entry_filesize($zip_entry),
                    'compressed_size' => zip_entry_compressedsize($zip_entry),
                    'folder' => $zip_folder
                );
            }
            zip_close($arch);
            return $filenames;
        }
    }
    return false;
}

function get_image_exts()
{
    return array('ico', 'gif', 'jpg', 'jpeg', 'jpc', 'jp2', 'jpx', 'xbm', 'wbmp', 'png', 'bmp', 'tif', 'tiff', 'psd');
}

function get_video_exts()
{
    return array('webm', 'mp4', 'm4v', 'ogm', 'ogv', 'mov');
}

function get_audio_exts()
{
    return array('wav', 'mp3', 'ogg', 'm4a');
}


function get_text_exts()
{
    return array(
        'txt', 'css', 'ini', 'conf', 'log', 'htaccess', 'passwd', 'ftpquota', 'sql', 'js', 'json', 'sh', 'config',
        'php', 'php4', 'php5', 'phps', 'phtml', 'htm', 'html', 'shtml', 'xhtml', 'xml', 'xsl', 'm3u', 'm3u8', 'pls', 'cue',
        'eml', 'msg', 'csv', 'bat', 'twig', 'tpl', 'md', 'gitignore', 'less', 'sass', 'scss', 'c', 'cpp', 'cs', 'py',
        'map', 'lock', 'dtd', 'svg',
    );
}

function get_text_mimes()
{
    return array(
        'application/xml',
        'application/javascript',
        'application/x-javascript',
        'image/svg+xml',
        'message/rfc822',
    );
}

function get_filesize($size)
{
    if ($size < 1000) {
        return sprintf('%s B', $size);
    } elseif (($size / 1024) < 1000) {
        return sprintf('%s KiB', round(($size / 1024), 2));
    } elseif (($size / 1024 / 1024) < 1000) {
        return sprintf('%s MiB', round(($size / 1024 / 1024), 2));
    } elseif (($size / 1024 / 1024 / 1024) < 1000) {
        return sprintf('%s GiB', round(($size / 1024 / 1024 / 1024), 2));
    } else {
        return sprintf('%s TiB', round(($size / 1024 / 1024 / 1024 / 1024), 2));
    }
}

function get_file_icon_class($path)
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    switch ($ext) {
        case 'ico': case 'gif': case 'jpg': case 'jpeg': case 'png':
        $img = 'fas fa-image';
        break;
        case 'txt': case 'css': case 'ini': case 'conf': case 'log': case 'htaccess':
        case 'sql': case 'js': case 'json': case 'sh': case 'less': case 'sass': case 'scss':
        $img = 'fas fa-file-alt';
        break;
        case 'zip': case 'rar': case 'gz': case 'tar': case '7z':
        $img = 'fas fa-file-archive';
        break;
        case 'php':
            $img = 'fab fa-php';
            break;
        case 'htm': case 'html': case 'shtml': case 'xhtml':
        case 'xml': case 'xsl': case 'svg':
        $img = 'fas fa-file-code';
        break;
        case 'mp3': case 'wma':
        $img = 'fas fa-file-audio';
        break;
        case 'avi': case 'mpg': case 'mpeg': case 'mp4': case 'flv': case '3gp':
        $img = 'fas fa-file-video';
        break;
        case 'xls': case 'xlsx':
        $img = 'fas fa-file-excel';
        break;
        case 'csv':
            $img = 'fas fa-file-csv';
            break;
        case 'doc': case 'docx':
        $img = 'fas fa-file-word';
        break;
        case 'pdf':
            $img = 'fas fa-file-pdf';
            break;
        case 'psd':
            $img = 'fab fa-adobe';
            break;
        default:
            $img = 'fas fa-file';
    }

    return $img;
}

function ffdelete($path)
{
    if (is_link($path)) {
        return unlink($path);
    } elseif (is_dir($path)) {
        $objects = scandir($path);
        $ok = true;
        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file != '.' && $file != '..') {
                    if (!ffdelete($path . '/' . $file)) {
                        $ok = false;
                    }
                }
            }
        }
        return ($ok) ? rmdir($path) : false;
    } elseif (is_file($path)) {
        return unlink($path);
    }
    return false;
}

function show_navigation($path)
{
    ?>
    <div class="navigation">
        <div class = "actions" id = "navigation-actions">
            <a title="Upload files" href="?path=<?php echo urlencode(PATH) ?>&amp;upload"><i class="fas fa-file-upload"></i></a>
            <a title="Add folder" href="#" onclick="addfolder('<?php echo PATH ?>');return false;"><i class="fas fa-folder-plus"></i></a>
        </div>
        <?php
        $path = clean_path($path);
        $root_url = "<a href='?path='><i class='fas fa-home' title='" . ROOT_PATH . "'></i></a> ";
        if ($path != '') {
            $exploded = explode('/', $path);
            $count = count($exploded);
            $array = array();
            $parent = '';
            for ($i = 0; $i < $count; $i++) {
                $parent = trim($parent . '/' . $exploded[$i], '/');
                $parent_enc = urlencode($parent);
                $array[] = "<a href='?path={$parent_enc}'>" .$exploded[$i]. "</a>";
            }
            $root_url .= implode(' >> ', $array);
        }
        echo '<div class="breadcrumbs">' . $root_url . '</div>';
        ?>
    </div>
    <?php
}

function html_header()
{
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>File Manager</title>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <style>
            body{padding:0;font:16px Arial,sans-serif;color:#234;background:#efefef}
            a{color:#296ea5;}a:hover{color:#b03}img{vertical-align:middle;border:none}
            a img{border:none}span.filesize{color:#789}small{font-size:11px;color:#999}p{margin-bottom:10px}
            ul{margin-left:2em;margin-bottom:10px}ul{list-style-type:none;margin-left:0}ul li{padding:3px 0}
            table{border-collapse:collapse;border-spacing:0;margin-bottom:10px;width:100%}
            th,td{padding:4px 7px;text-align:left;vertical-align:top;border:1px solid #ddd;background:#fff;white-space:nowrap}
            th,td.filesize{background-color:#eee}td.filesize span{color:#222}
            tr:hover td{background-color:#f5f5f5}tr:hover td.filesize{background-color:#eee}
            #wrapper{max-width:1000px;min-width:400px;margin:10px auto}
            .filename{max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .navigation{padding:15px 15px;border:1px solid #ddd;background-color:#fff;margin-bottom:10px}
            .navigation>.actions{float: right}
            .breadcrumbs{word-wrap:break-word}
            #navigation-actions i {font-size: 24px}
            i {font-size: 16px}
        </style>


    </head>
    <body>
    <div id="wrapper">
    <?php
}

function html_footer()
{
    ?>
    </div>
    <script type="text/javascript">
        function rename(p,f){var n=prompt('New filename',f);if(n!==null&&n!==''&&n!=f){window.location.search='path='+encodeURIComponent(p)+'&action=rename&oldname='+encodeURIComponent(f)+'&newname='+encodeURIComponent(n);}}
        function addfolder(p){var n=prompt('Add folder','foldername');if(n!==null&&n!==''){window.location.search='path='+encodeURIComponent(p)+'&action=addfolder&foldername='+encodeURIComponent(n);}}
    </script>
    </body>
    </html>
    <?php
}
