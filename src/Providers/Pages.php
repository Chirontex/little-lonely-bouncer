<?php
/**
 * Little Lonely Bouncer
 */
namespace LittleLonelyBouncer\Providers;

use LittleLonelyBouncer\Exceptions\PagesException;
use LittleLonelyBouncer\Exceptions\ErrorsList;
use wpdb;

class Pages
{

    protected $wpdb;
    protected $table = 'llb_pages';

    public function __construct(wpdb $wpdb)
    {
        
        $this->wpdb = $wpdb;

        $this->createTable();

    }

    public function createTable() : void
    {

        if ($this->wpdb->query(
            "CREATE TABLE `".$this->wpdb->prefix.$this->table."` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `key` VARCHAR(255) NOT NULL,
                `value` LONGTEXT NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8mb4_unicode_ci'
            AUTO_INCREMENT=0"
        ) === false) {

            $error = ErrorsList::pages(-1);

            throw new PagesException($error['message'], $error['code']);

        }

    }

}
