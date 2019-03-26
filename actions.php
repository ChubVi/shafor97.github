<?php
// Rename
if (isset($_GET['action'],$_GET['oldname'], $_GET['newname']) && $_GET['action'] == 'rename') {
    $old = $_GET['oldname'];
    $old = clean_path($old);
    $old = str_replace('/', '', $old);
    $new = $_GET['newname'];
    $new = clean_path($new);
    $new = str_replace('/', '', $new);
    $path = ROOT_PATH;
    if (PATH != '') {
        $path .= '/' . PATH;
    }
    // rename
    if ($old != '' && $new != '' && !file_exists($path . '/' . $new) && file_exists($path . '/' . $old)) {
        rename($path . '/' . $old, $path . '/' . $new);
    } else {
        var_dump('Name not set');die;
    }
    redirect($_SERVER['PHP_SELF'] . '?path=' . urlencode(PATH));
}

// Create folder
if (isset($_GET['foldername'], $_GET['action']) && $_GET['action'] == 'addfolder') {
    $name = strip_tags($_GET['foldername']); // remove unwanted characters from folder name
    $name = clean_path($name);
    $name = str_replace('/', '', $name);
    if ($name != '' && $name != '..' && $name != '.') {
        $path = ROOT_PATH;
        if (PATH != '') {
            $path .= '/' . PATH;
        }
        if (!file_exists($path . '/' . $name) && mkdir($path . '/' . $name, 0777, true)) {
            //success folder create
        } elseif (file_exists($path . '/' . $name)) {
            var_dump("Folder $name already exists");die;
        } else {
            var_dump('Can not create new folder');die;
        }
    } else {
        var_dump('Can not create new folder, wrong folder name');die;
    }
    redirect($_SERVER['PHP_SELF'] . '?path=' . urlencode(PATH));
}

// Upload
if (isset($_POST['action']) && $_POST['action'] == 'upload') {
    $path = ROOT_PATH;
    if (PATH != '') {
        $path .= '/' . PATH;
    }

    $errors = 0;
    $uploads = 0;
    $total = count($_FILES['upload']['name']);

    for ($i = 0; $i < $total; $i++) {
        $tmp_name = $_FILES['upload']['tmp_name'][$i];
        if (empty($_FILES['upload']['error'][$i]) && !empty($tmp_name) && $tmp_name != 'none') {
            if (move_uploaded_file($tmp_name, $path . '/' . $_FILES['upload']['name'][$i])) {
                $uploads++;
            } else {
                $errors++;
            }
        }
    }

    if ($errors == 0 && $uploads > 0) {
        //success upload
    } elseif ($errors == 0 && $uploads == 0) {
        var_dump('Nothing to upload');die;
    } else {
        var_dump('Error while uploading files.', $uploads);die;
    }

    redirect($_SERVER['PHP_SELF']. '?path=' . urlencode(PATH));
}

// upload form
if (isset($_GET['upload']))
{
    html_header();
    show_navigation(PATH);
?>

    <div class="navigation">
        <p><b>Uploading files</b></p>
        <p class="breadcrumbs">Destination folder: <?php echo ROOT_PATH . '/' . PATH; ?></p>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="path" value="<?php echo PATH ?>">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="upload[]"><br>
            <input type="file" name="upload[]"><br>
            <input type="file" name="upload[]"><br>
            <input type="file" name="upload[]"><br>
            <input type="file" name="upload[]"><br>
            <br>
            <p>
                <button class="btn"><i class="fas fa-file-upload"></i> Upload</button> &nbsp;
                <b><a href="?path=<?php echo urlencode(PATH) ?>"><i class="fas fa-window-close"></i> Cancel</a></b>
            </p>
        </form>
    </div>
    <?php
    html_footer();
    exit;
}

// Download
if (isset($_GET['download'])) {
    $download = $_GET['download'];
    $download = clean_path($download);
    $download = str_replace('/', '', $download);
    $path = ROOT_PATH;
    if (PATH != '') {
        $path .= '/' . PATH;
    }
    if ($download != '' && is_file($path . '/' . $download)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path . '/' . $download) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path . '/' . $download));
        readfile($path . '/' . $download);
        exit;
    } else {
        redirect($_SERVER['PHP_SELF'] . '?path=' . urlencode(PATH));
    }
}

// Delete file / folder
if (isset($_GET['delete'])) {
    $delete = $_GET['delete'];
    $delete = clean_path($delete);
    $delete = str_replace('/', '', $delete);
    if ($delete != '' && $delete != '..' && $delete != '.') {
        $path = ROOT_PATH;
        if (PATH != '') {
            $path .= '/' . PATH;
        }
        if (ffdelete($path . '/' . $delete)) {
            //success
        } else {
            var_dump('File or folder not deleted.');die;
        }
    } else {
        var_dump('Wrong file or folder name.');die;
    }
    redirect($_SERVER['PHP_SELF'] . '?path=' . urlencode(PATH));
}

// file viewer
if (isset($_GET['view'])) {
    $file = $_GET['view'];
    $file = clean_path($file);
    $file = str_replace('/', '', $file);
    if ($file == '' || !is_file($path . '/' . $file)) {
        redirect($_SERVER['PHP_SELF'] . '?path=' . urlencode(PATH));
    }
    html_header();
    show_navigation(PATH);

    $file_url = ROOT_URL .FM_PATH != '' ? '/' . FM_PATH : ''. '/' . $file;
    $file_path = $path . '/' . $file;
    $mime_type = get_mime_type($file_path);
    $filesize = filesize($file_path);

    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

    $is_zip = false;
    $is_image = false;
    $is_audio = false;
    $is_video = false;
    $is_text = false;

    $view_title = 'File';
    $filenames = false; // for zip
    $content = ''; // for text

    if ($ext == 'zip') {
        $is_zip = true;
        $view_title = 'Archive';
        $filenames = get_zip_info($file_path);
    } elseif (in_array($ext, get_image_exts())) {
        $is_image = true;
        $view_title = 'Image';
    } elseif (in_array($ext, get_audio_exts())) {
        $is_audio = true;
        $view_title = 'Audio';
    } elseif (in_array($ext, get_video_exts())) {
        $is_video = true;
        $view_title = 'Video';
    } elseif (in_array($ext, get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, get_text_mimes())) {
        $is_text = true;
        $content = file_get_contents($file_path);
    }

    ?>
    <div class="navigation">
        <p class="breadcrumbs"><b><?php echo $view_title ?> "<?=$file?>"</b></p>
        <p class="breadcrumbs">
            Full path: <?php echo $file_path?><br>
            File size: <?php echo get_filesize($filesize) ?><?php if ($filesize >= 1000): ?> (<?php echo sprintf('%s bytes', $filesize) ?>)<?php endif; ?><br>
            MIME-type: <?php echo $mime_type ?><br>
            <?php
            // ZIP info
            if ($is_zip && $filenames !== false) {
                $total_files = 0;
                $total_comp = 0;
                $total_uncomp = 0;
                foreach ($filenames as $fn) {
                    if (!$fn['folder']) {
                        $total_files++;
                    }
                    $total_comp += $fn['compressed_size'];
                    $total_uncomp += $fn['filesize'];
                }
                ?>
                Files in archive: <?php echo $total_files ?><br>
                Total size: <?php echo get_filesize($total_uncomp) ?><br>
                Size in archive: <?php echo get_filesize($total_comp) ?><br>
                Compression: <?php echo round(($total_comp / $total_uncomp) * 100) ?>%<br>
                <?php
            }
            // Image info
            if ($is_image) {
                $image_size = getimagesize($file_path);
                echo 'Image sizes: ' . (isset($image_size[0]) ? $image_size[0] : '0') . ' x ' . (isset($image_size[1]) ? $image_size[1] : '0') . '<br>';
            }
            // Text info
            if ($is_text) {
                $is_utf8 = is_utf8($content);
                if (function_exists('iconv')) {
                    if (!$is_utf8) {
                        $content = iconv('CP1251', 'UTF-8//IGNORE', $content);
                    }
                }
                echo 'Charset: ' . ($is_utf8 ? 'utf-8' : '8 bit') . '<br>';
            }
            ?>
        </p>
        <p>
            <b><a href="?path=<?php echo urlencode(PATH) ?>&amp;download=<?php echo urlencode($file) ?>"><i class="fas fa-file-download"></i> Download</a></b> &nbsp;
            <b><a href="?path=<?php echo urlencode(PATH) ?>"> Back</a></b>
        </p>
        <?php
        if ($is_zip) {
            // ZIP content
            if ($filenames !== false) {
                echo '<code class="maxheight">';
                foreach ($filenames as $fn) {
                    if ($fn['folder']) {
                        echo '<b>' . $fn['name'] . '</b><br>';
                    } else {
                        echo $fn['name'] . ' (' . get_filesize($fn['filesize']) . ')<br>';
                    }
                }
                echo '</code>';
            } else {
                echo '<p>Error while fetching archive info</p>';
            }
        } elseif ($is_image) {
            // Image content
            if (in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'))) {
                echo '<p><img src="' . $file_url . '" alt="" class="preview-img"></p>';
            }
        } elseif ($is_audio) {
            // Audio content
            echo '<p><audio src="' . $file_url . '" controls preload="metadata"></audio></p>';
        } elseif ($is_video) {
            // Video content
            echo '<div class="preview-video"><video src="' . $file_url . '" width="640" height="360" controls preload="metadata"></video></div>';
        } elseif ($is_text) {
            if (in_array($ext, array('php', 'php4', 'php5', 'phtml', 'phps'))) {
                // php highlight
                $content = highlight_string($content, true);
            } else {
                $content = '<pre>' . $content. '</pre>';
            }
            echo $content;
        }
        ?>
    </div>
    <?php
    html_footer();
    exit;
}


