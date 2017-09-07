<?php
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}
// 账号设置
function lin_weibo_pic_settings()
{
    if (!is_admin()) {
        return;
    }
    global $wb_uploader;
    $wb_pic_url = '';
    $wb_pic_url2 = '';
    ?>
    <div class="wrap">
        <h2><?php _e('WeiBo Account Settings', 'lin_weibo_pic'); ?></h2>
        <?php
        if ($_POST['update_options'] == 'true') {//若提交了表单，则保存变量
            update_option(LIN_WB_USERNAME, $_POST['username']);
            update_option(LIN_WB_PASSWORD, $_POST['password']);
            echo '<div id="message" class="updated below-h2"><p>' . __('Saved', 'lin_weibo_pic') . '</p></div>';
            $wb_uploader = \Lin\WeiBoUploader::newInstance(get_option(LIN_WB_USERNAME), get_option(LIN_WB_PASSWORD));
            update_option(LIN_WB_COOKIE, $wb_uploader->getCookie());
        }
        $name = get_option(LIN_WB_USERNAME);
        $pass = get_option(LIN_WB_PASSWORD);
        $cook = get_option(LIN_WB_COOKIE);
        $wb_uploader = \Lin\WeiBoUploader::newInstance($name, $pass, $cook);
        if ($_POST['url']) {
            $url = $_POST['url'];
            if ($wb_uploader == null) {
                echo '<div class="error below-h2"><p>' . __('Please set your username and password first.', 'lin_weibo_pic') . '</p></div>';
            } else {
                try {
                    $pid = $wb_uploader->upload($url, false);
                    $wb_pic_url = $wb_uploader->getImageUrl($pid);
                } catch (\Lin\WeiBoException $e) {
                    printf('<div  class="error below-h2"><p>' . __('Error: %1$s', 'lin_weibo_pic') . '</p></div>', $e->getMessage());
                }
            }
        }
        if ($_FILES['file']) {
            if ($_FILES["file"]["error"] > 0) {
                printf('<div class="error below-h2"><p>' . __('Error: %1$s', 'lin_weibo_pic') . '</p></div>', $_FILES["file"]["error"]);
            } else {
                printf('<div class="notice below-h2"><p>'
                    . __('Uploaded : %1$s', 'lin_weibo_pic') . "<br />"
                    . __('Type     : %2$s', 'lin_weibo_pic') . "<br />"
                    . __('Size     : %3$s KB', 'lin_weibo_pic') . "<br />"
                    . __('Stored in: %4$s', 'lin_weibo_pic')
                    . '</p></div>',
                    $_FILES["file"]["name"],
                    $_FILES["file"]["type"],
                    $_FILES["file"]["size"] / 1024,
                    $_FILES["file"]["tmp_name"]
                );
            }
            if ($wb_uploader == null) {
                echo '<div class="error below-h2"><p>' . __('Please set your username and password first.', 'lin_weibo_pic') . '</p></div>';
            } else {
                $file = $_FILES['file']['tmp_name'];
                try {
                    $pid = $wb_uploader->upload($file);
                    $wb_pic_url2 = $wb_uploader->getImageUrl($pid);
                } catch (\Lin\WeiBoException $e) {
                    printf('<div  class="error below-h2"><p>' . __('Error: %1$s', 'lin_weibo_pic') . '</p></div>', $e->getMessage());
                }
            }
        }
        //下面开始界面表单
        ?>
        <form method="POST" action="">
            <input type="hidden" name="update_options" value="true"/>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="username"><?php _e('Username', 'lin_weibo_pic'); ?></label></th>
                    <td><input name="username" type="text" id="username" class="regular-text"
                               value="<?php echo get_option(LIN_WB_USERNAME) ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="password"><?php _e('Password', 'lin_weibo_pic'); ?></label></th>
                    <td><input name="password" type="password" id="password" class="regular-text"
                               value="<?php echo get_option(LIN_WB_PASSWORD) ?>"></td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" name="admin_options"
                      value="<?php _e('Update', 'lin_weibo_pic'); ?>"/></p>
        </form>
        <hr>
        <h2><?php _e('Test upload to WeiBo', 'lin_weibo_pic'); ?></h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="url"><?php _e('Image Url', 'lin_weibo_pic'); ?></label></th>
                    <td><input name="url" type="text" id="url" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="file"><?php _e('Image File', 'lin_weibo_pic'); ?></label></th>
                    <td><input name="file" type="file" id="file" class="regular-text"></td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" name="admin_options"
                      value="<?php _e('Upload', 'lin_weibo_pic'); ?>"/></p>
        </form>

        <?php if ($wb_pic_url) {
            echo '<p>' . $wb_pic_url . '</p><br>' . '<img src="' . $wb_pic_url . '" style="max-width:100%;;">';
        }
        if ($wb_pic_url2) {
            echo '<p>' . $wb_pic_url2 . '</p><br>' . '<img src="' . $wb_pic_url2 . '" style="max-width:100%;;">';
        }
        ?>
    </div>
    <?php
}//lin_weibo_pic_settings()

add_action('admin_menu', 'add_page');
function add_page()
{
    add_options_page(__('WeiBo Pic Settings'), __('WeiBo Pic Settings'), 'administrator', 'weibo-pic-settings', 'lin_weibo_pic_settings');
}