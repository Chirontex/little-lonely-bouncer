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

            $this->adminPageRevive();

        }

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

}
