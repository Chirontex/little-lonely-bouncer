<?php
/**
 * Little Lonely Bouncer
 */
namespace LittleLonelyBouncer;

use LittleLonelyBouncer\Providers\Pages;
use LittleLonelyBouncer\Exceptions\PagesException;

final class Main
{

    private $path;
    private $url;
    private $wpdb;
    private $admin_file = 'little-lonely-bouncer-admin.php';
    private $admin_notice;
    private $pages_body;
    private $protection_check_fail = '';
    private $page_data;

    public function __construct(string $path, string $url)
    {
        
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->path = $path;
        $this->url = $url;

        $this->adminMenuInit();

        if (strpos(
            $_GET['page'],
            $this->admin_file
        ) !== false) {

            if (isset($_POST['llb-page-add-uri']) &&
                isset($_POST['llb-page-add-passwords'])) $this->adminPageAdd();

            $this->adminPageRevive();

        }

        $this->pageProtectionCheck();

    }

    private function adminMenuInit() : void
    {

        add_action('admin_menu', function() {

            add_menu_page(
                'Защита страниц',
                'Защита страниц',
                8,
                $this->path.$this->admin_file
            );

        });

    }

    private function adminPageRevive() : void
    {

        add_action('admin_enqueue_scripts', function() {

            wp_enqueue_style(
                'bootstrap-min',
                (file_exists($this->path.'css/bootstrap.min.css') ?
                    $this->url.'css/bootstrap.min.css' :
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css'
                ),
                [],
                '5.0.0-beta2'
            );

            wp_enqueue_style(
                'llb-admin',
                $this->url.'css/admin.css',
                [],
                '1.0.0'
            );

            wp_enqueue_script(
                'bootstrap-bundle-min',
                (file_exists($this->path.'js/bootstrap.bundle.min.js') ?
                    $this->url.'js/bootstrap.min.js' :
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js'
                ),
                [],
                '5.0.0-beta2'
            );

            wp_enqueue_script(
                'llb-admin',
                $this->url.'js/admin.js',
                [],
                '1.0.0'
            );

        });

        add_action('init', function() {

            $pages_provider = new Pages($this->wpdb);

            $pages_all = $pages_provider->getAllPages();

            if (!empty($pages_all)) {

                ob_start();

                foreach ($pages_all as $page_id => $page_values) {

                    $passwords = $page_values['passwords'];

                    if (iconv_strlen($passwords) > 100) {

                        $passwords = explode(', ', $passwords);
                        $passwords = array_slice($passwords, 0, 10);
                        $passwords = implode(', ', $passwords);

                    }

?>
<tr id="llb-page-<?= $page_id ?>">
    <td class="text-center"><?= $page_id ?></td>
    <td id="llb-page-uri-<?= $page_id ?>" class="text-center"><?= htmlspecialchars($page_values['page_uri']) ?></td>
    <td id="llb-page-passwords-<?= $page_id ?>" class="text-center"><?= htmlspecialchars($passwords) ?></td>
</tr>
<?php

                }

                $this->pages_body = ob_get_clean();

                add_filter('llb-pages-tbody', function() {

                    return $this->pages_body;

                });

            }

        });

    }

    private function adminPageAdd() : void
    {

        add_action('plugins_loaded', function() {

            if (wp_verify_nonce(
                $_POST['llb-pages-wpnp'],
                'llb-page-add'
            ) === false) $this->adminNotice(
                'danger',
                'Произошла ошибка отправки формы. Попробуйте ещё раз.'
            );
            else {

                $uri = $_POST['llb-page-add-uri'];

                if (strpos($uri, '?') !== false) $uri = substr(
                    $uri,
                    0,
                    strpos($uri, '?')
                );

                if ($uri !== '/') $uri = trim($uri, '/');

                $passwords = $_POST['llb-page-add-passwords'];

                $pages_provider = new Pages($this->wpdb);

                try {

                    $page_id = $pages_provider->pageSet($uri);

                    $pages_provider->valueAdd(
                        $page_id,
                        'passwords',
                        $passwords
                    );

                } catch (PagesException $e) {

                    $this->adminNotice(
                        'danger',
                        'Ошибка при добавлении защиты на страницу. '.
                        $e->getCode().': "'.$e->getMessage().'"'
                    );

                }

                if (!isset($e)) $this->adminNotice(
                    'success',
                    'Защита на страницу успешно добавлена!'
                );

            }

        });

    }

    /**
     * Output the admin notice.
     * 
     * @param string $type
     * Available types: 'success', 'warning', 'error' (or 'danger').
     * 
     * @param string $text
     * 
     * @return void
     */
    private function adminNotice(string $type, string $text) : void
    {

        if ($type === 'danger') $type = 'error';

        $this->admin_notice = [
            'type' => $type,
            'text' => $text
        ];

        add_action('admin_notices', function($prev_notices) {

            ob_start();

?>
<div class="notice notice-<?= htmlspecialchars($this->admin_notice['type']) ?> is-dismissible" style="max-width: 500px; margin-left: auto; margin-right: auto;">
    <p style="text-align: center;"><?= $this->admin_notice['text'] ?></p>
</div>
<?php

            echo $prev_notices.ob_get_clean();

        });

    }

    private function pageProtectionCheck() : void
    {

        $uri = $_SERVER['REQUEST_URI'];

        if (strpos($uri, '?') !== false) $uri = substr(
            $uri,
            0,
            strpos($uri, '?')
        );

        if ($uri !== '/') $uri = trim($uri, '/');

        $pages_provider = new Pages($this->wpdb);

        $this->page_data = $pages_provider->pageGetByUri($uri);

        if (!empty($this->page_data)) {

            add_action('wp_enqueue_scripts', function() {

                wp_enqueue_style(
                    'llb-protection',
                    $this->url.'css/protection.css',
                    [],
                    '1.0.0'
                );

                wp_enqueue_script(
                    'llb-protection',
                    $this->url.'js/protection.js',
                    [],
                    '1.0.0'
                );

            });

            add_action('plugins_loaded', function() {

                $protect = true;

                if (isset($_POST['llb-protection-email'])) {

                    if (wp_verify_nonce(
                        $_POST['llb-protection-wpnp'],
                        'llb-protection'
                    ) === false) $this->protection_check_fail = 'Произошла ошибка при отправке формы. Попробуйте ещё раз.';
                    else {
                        
                        setcookie(
                            'llb-page-'.$this->page_data['page_id'],
                            trim($_POST['llb-protection-email']),
                            0,
                            '/'
                        );

                        $_COOKIE['llb-page-'.$this->page_data['page_id']] = trim($_POST['llb-protection-email']);
                
                    }

                }

                if (isset($_COOKIE['llb-page-'.$this->page_data['page_id']])) {

                    $passwords = explode(',', $this->page_data['passwords']);

                    $passwords = array_map(function($value) {

                        return trim($value);

                    }, $passwords);

                    if (array_search(
                        $_COOKIE['llb-page-'.$this->page_data['page_id']],
                        $passwords
                    ) === false) $this->protection_check_fail = 'Участник с данным e-mail не найден, либо e-mail указан неверно.';
                    else $protect = false;

                }

                if ($protect) {

                    add_filter('the_content', function() {

                        ob_start();

?>
<div class="block-centered-limited">
    <h4 class="text-centered">Подтвердите ваше присутствие на вебинаре</h4>
    <p>Пожалуйста, введите e-mail, который вы указывали при регистрации на данный вебинар как участник.</p>
<?php

                        if (!empty($this->protection_check_fail)) {
                        
?>
    <p class="text-red"><?= $this->protection_check_fail ?></p>
<?php

                        }

?>
    <form action="" method="post">
        <div class="margin-bottom-1">
            <input type="text" id="llb-protection-email" name="llb-protection-email" placeholder="Ваш e-mail участника" oninput="protectionFormSubmitCheck();">
        </div>
        <?php wp_nonce_field('llb-protection', 'llb-protection-wpnp') ?>
        <div class="margin-bottom-1 text-centered">
            <button type="submit" id="llb-protection-submit" class="button button-primary" disabled="true">Подтвердить</button>
        </div>
    </form>
</div>
<?php

                        return ob_get_clean();

                    }, 1000000000);

                }

            });

        }

    }

}
