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

    /**
     * Create the table.
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function createTable() : void
    {

        if ($this->wpdb->query(
            "CREATE TABLE `".$this->wpdb->prefix.$this->table."` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `page_id` BIGINT UNSIGNED NOT NULL,
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

    /**
     * Get page data by its ID.
     * 
     * @param int $id
     * Page ID cannot be lesser than 1.
     * 
     * @return array
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function pageGetById(int $id) : array
    {

        if ($id < 1) {

            $error = ErrorsList::pages(-3);

            throw new PagesException($error['message'], $error['code']);

        }

        $select = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT t.key, t.value
                    FROM `".$this->wpdb->prefix.$this->table."` AS t
                    WHERE t.page_id = %d",
                $id
            ),
            ARRAY_A
        );

        $result = [];

        if (!empty($select)) {

            foreach ($select as $row) {

                $result[$row['key']] = $row['value'];

            }

            $result['page_id'] = $id;

        }

        return $result;

    }

    /**
     * Get page data by its URI.
     * 
     * @param string $uri
     * Page URI cannot be empty.
     * 
     * @return array
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function pageGetByUri(string $uri) : array
    {

        if (empty($uri)) {

            $error = ErrorsList::pages(-5);

            throw new PagesException($error['message'], $error['code']);

        }

        $select = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT t.page_id, t1.key, t1.value
                    FROM `".$this->wpdb->prefix.$this->table."` AS t
                    INNER JOIN `".$this->wpdb->prefix.$this->table."` AS t1
                    ON t.page_id = t1.page_id
                    WHERE t.key = 'page_uri'
                    AND t.value = %s
                    AND t1.key != 'page_uri'",
                $uri
            ),
            ARRAY_A
        );

        $result = [];

        if (!empty($select)) {

            foreach ($select as $row) {

                if (!isset($result['page_id'])) $result['page_id'] = $row['page_id'];

                $result[$row['key']] = $row['value'];

            }

            $result['page_uri'] = $uri;

        }

        return $result;

    }

    /**
     * Set the page.
     * 
     * @param string $uri
     * Page URI cannot be empty.
     * 
     * @return int
     * Page ID.
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function pageSet(string $uri) : int
    {

        if (empty($uri)) {

            $error = ErrorsList::pages(-5);

            throw new PagesException($error['message'], $error['code']);

        }

        $select = $this->wpdb->get_results(
            "SELECT t.page_id
                FROM `".$this->wpdb->prefix.$this->table."` AS t
                WHERE t.key = 'page_uri'
                ORDER BY t.page_id
                DESC
                LIMIT 1",
            ARRAY_A
        );

        if (empty($select)) $page_id = 0;
        else $page_id = (int)$select[0]['page_id'];

        $page_id += 1;

        if ($this->wpdb->insert(
            $this->wpdb->prefix.$this->table,
            [
                'page_id' => $page_id,
                'key' => 'page_uri',
                'value' => $uri
            ],
            ['%d', '%s', '%s']
        ) === false) {

            $error = ErrorsList::pages(-6);

            throw new PagesException($error['message'], $error['code']);

        }

        return $page_id;

    }

    /**
     * Add the value.
     * 
     * @param int $page_id
     * Page ID cannot be lesser than 1.
     * 
     * @param string $key
     * 
     * @param string $value
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function valueAdd(int $page_id, string $key, string $value) : void
    {

        if ($page_id < 1) {

            $error = ErrorsList::pages(-3);

            throw new PagesException($error['message'], $error['code']);

        }

        $key = trim($key);
        $value = trim($value);

        if ($key === 'page_uri') {

            $error = ErrorsList::pages(-7);

            throw new PagesException($error['message'], $error['code']);

        }

        if ($this->wpdb->insert(
            $this->wpdb->prefix.$this->table,
            [
                'key' => $key,
                'value' => $value
            ],
            ['%s', '%s']
        ) === false) {

            $error = ErrorsList::pages(-2);

            throw new PagesException(
                $error['message'].' ('.$key.': '.$value.')',
                $error['code']
            );

        }

    }

}
