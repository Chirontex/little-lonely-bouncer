<?php
/**
 * Plugin Name: Little Lonely Bouncer
 * Plugin URI: https://github.com/chirontex/little-lonely-bouncer
 * Description: Этот плагин позволяет реализовать простейшую проверку для доступа к странице.
 * Version: 0.1.0
 * Author: Дмитрий Шумилин
 * Author URI: mailto://chirontex@yandex.com
 */
use LittleLonelyBouncer\Main;

require_once __DIR__.'/little-lonely-bouncer-autoload.php';

new Main(
    plugin_dir_path(__FILE__),
    plugin_dir_url(__FILE__)
);
