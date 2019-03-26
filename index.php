<?php
require 'functions.php';

$root_url = '';
$root_path = $_SERVER['DOCUMENT_ROOT'];
$root_path = rtrim($root_path, '\\/');
$root_path = str_replace('\\', '/', $root_path);
define('ROOT_PATH',$root_path);

if (!is_dir($root_path))
{
    echo "<h1>Root path $root_path not found!</h1>";
    exit;
}

$root_url = clean_path($root_url);
defined('ROOT_URL') || define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . (!empty($root_url) ? '/' . $root_url : ''));

$root_url = clean_path($root_url);

if (!isset($_GET['path']))
{
    redirect($_SERVER['PHP_SELF'] . '?path=');
}elseif(isset($_GET['path']))
{
    $path = $_GET['path'];
}elseif(isset($_POST['path']))
{
    $path = $_POST['path'];
}

$path = clean_path($path);
define('PATH', $path);
$path = ROOT_PATH;

if (PATH != '')
{
    $path = ROOT_PATH . '/' . PATH;
}

if (!is_dir($path))
{
    redirect($_SERVER['PHP_SELF'] . '?path=');
}
$parent = get_parent_path(PATH);

$objects = is_readable($path) ? scandir($path) : array();
$folders = array();
$files = array();
if (is_array($objects))
{
    foreach ($objects as $file)
    {
        if ($file == '.' || $file == '..')
            continue;

        $new_path = $path . '/' . $file;
        if (is_file($new_path))
        {
            $files[] = $file;
        } elseif (is_dir($new_path) && $file != '.' && $file != '..')
        {
            $folders[] = $file;
        }
    }
}

if (!empty($files)) {
    natcasesort($files);
}
if (!empty($folders)) {
    natcasesort($folders);
}
require_once 'actions.php';

html_header();
show_navigation(PATH);

$num_files = count($files);
$num_folders = count($folders);
$all_files_size = 0;

?>
    <form action="" method="post">
        <input type="hidden" name="path" value="<?= PATH ?>">
        <table><tr>
                <th> Actions</th>
                <th>Name</th><th>Size</th>
            <?php if ($parent !== false) {?>
                <tr><td></td><td colspan="4"><a href="?path=<?php echo urlencode($parent) ?>"><i class="fas fa-level-up-alt"></i> .. </a></td></tr>
                <?php
            }
            foreach ($folders as $f) :
            ?>
                <tr>
                    <td>
                        <a title="Rename" href="#" onclick="rename('<?php echo PATH ?>', '<?php echo $f ?>//');return false;"><i class="fas fa-text-width"></i></a>
                        <a title="Delete" href="?path=<?php echo urlencode(PATH) ?>&amp;delete=<?php echo urlencode($f) ?>" onclick="return confirm('Delete folder?');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                    <td><div class="filename"><a href="?path=<?php echo urlencode(trim(PATH . '/' . $f, '/')) ?>"><i class="fas fa-folder"></i> <?php echo $f ?></a></div></td>
                    <td>Folder</td>
                </tr>
            <?php
            endforeach;
            foreach ($files as $f):
                $icon = get_file_icon_class($path . '/' . $f);
                $filesize_raw = filesize($path . '/' . $f);
                $filesize = get_filesize($filesize_raw);
                $filelink = '?path=' . urlencode(PATH) . '&view=' . urlencode($f);
                $all_files_size += $filesize_raw;
                ?>
                <tr>
                    <td>
                    <a title="Rename" href="#" onclick="rename('<?=PATH?>','<?=$f?>');return false;"><i class="fas fa-text-width"></i></a>
                    <a title="Download" href="?path=<?=urlencode(PATH)?>&amp;download=<?=urlencode($f)?>"><i class="fas fa-file-download"></i></a>
                    <a title="Delete" href="?path=<?=urlencode(PATH)?>&amp;delete=<?=urlencode($f)?>" onclick="return confirm('Delete file?');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                    <td><div class="filename"><a href="<?=$filelink?>" title="View file"><i class="<?=$icon?>"></i> <?php echo $f ?></a></div></td>
                    <td><span class="filesize" title="<?php printf('%s bytes', $filesize_raw) ?>"><?php echo $filesize ?></span></td>
                </tr>
                <?php
            endforeach;
            if (empty($folders) && empty($files))
            {
                ?>
                <tr><td></td><td colspan="6"><em>Folder is empty</em></td></tr>
                <?php
            } else
            {
            ?>
                <tr>
                    <td class="filesize" colspan="6">
                        Full size: <span title="<?php printf('%s bytes', $all_files_size) ?>"><?php echo get_filesize($all_files_size) ?></span>,
                        files: <?php echo $num_files ?>,
                        folders: <?php echo $num_folders ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    </form>

<?php
html_footer();