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
    public function createTable()
    {

        if ($this->wpdb->query(
            "CREATE TABLE IF NOT EXISTS `".$this->wpdb->prefix.$this->table."` (
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
     * Get all pages.
     * 
     * @return array
     * 2-level associative array with pages IDs as a keys on the first level.
     */
    public function getAllPages() : array
    {

        $select = $this->wpdb->get_results(
            "SELECT *
                FROM `".$this->wpdb->prefix.$this->table."`",
            ARRAY_A
        );

        $result = [];

        if (!empty($select)) {

            foreach ($select as $row) {

                $result[$row['page_id']][$row['key']] = $row['value'];

            }

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
     * Remove the page with all its metadata.
     * 
     * @param int $page_id
     * Page ID cannot be lesser than 1.
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function pageRemove(int $page_id)
    {

        if ($page_id < 1) {

            $error = ErrorsList::pages(-3);

            throw new PagesException($error['message'], $error['code']);

        }

        $this->wpdb->delete(
            $this->wpdb->prefix.$this->table,
            ['page_id' => $page_id],
            ['%d']
        );

    }

    /**
     * Add the value.
     * 
     * @param int $page_id
     * Page ID cannot be lesser than 1.
     * 
     * @param string $key
     * Key cannot be empty.
     * 
     * @param string $value
     * Value cannot be empty.
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function valueAdd(int $page_id, string $key, string $value)
    {

        $key = trim($key);
        $value = trim($value);

        $error_code = 0;

        if ($page_id < 1) $error_code = -3;
        elseif (empty($key)) $error_code = -9;
        elseif ($key === 'page_uri') $error_code = -7;
        elseif (empty($value)) $error_code = -10;

        if ($error_code !== 0) {

            $error = ErrorsList::pages($error_code);

            throw new PagesException($error['message'], $error['code']);

        }

        if ($this->wpdb->insert(
            $this->wpdb->prefix.$this->table,
            [
                'page_id' => $page_id,
                'key' => $key,
                'value' => $value
            ],
            ['%d', '%s', '%s']
        ) === false) {

            $error = ErrorsList::pages(-2);

            throw new PagesException(
                $error['message'].' ('.$key.': '.$value.')',
                $error['code']
            );

        }

    }

    /**
     * Update the value.
     * 
     * @param int $page_id
     * Page ID cannot be lesser than 1.
     * 
     * @param string $key
     * Key cannot be empty.
     * 
     * @param string $value
     * Value cannot be empty.
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function valueUpdate(int $page_id, string $key, string $value)
    {

        $key = trim($key);
        $value = trim($value);

        $error_code = 0;

        if ($page_id < 1) $error_code = -3;
        elseif (empty($key)) $error_code = -9;
        elseif ($key === 'page_uri') $error_code = -7;
        elseif (empty($value)) $error_code = -10;

        if ($error_code !== 0) {

            $error = ErrorsList::pages($error_code);

            throw new PagesException($error['message'], $error['code']);

        }

        if ($this->wpdb->update(
            $this->wpdb->prefix.$this->table,
            ['value' => $value],
            [
                'page_id' => $page_id,
                'key' => $key
            ],
            ['%s'],
            ['%d', '%s']
        ) === false) {

            $error = ErrorsList::pages(-8);

            throw new PagesException($error['message'], $error['code']);

        }

    }

    /**
     * Delete the value.
     * 
     * @param int $page_id
     * Page ID cannot be lesser than 1.
     * 
     * @param string $key
     * Key cannot be empty.
     * 
     * @return void
     * 
     * @throws LittleLonelyBouncer\Exceptions\PagesException
     */
    public function valueDelete(int $page_id, string $key)
    {

        $key = trim($key);

        $error_code = 0;

        if ($page_id < 1) $error_code = -3;
        elseif (empty($key)) $error_code = -9;
        elseif ($key === 'page_uri') $error_code = -7;

        if ($error_code !== 0) {

            $error = ErrorsList::pages($error_code);

            throw new PagesException($error['message'], $error['code']);

        }

        $this->wpdb->delete(
            $this->wpdb->prefix.$this->table,
            [
                'page_id' => $page_id,
                'key' => $key
            ],
            ['%d', '%s']
        );

    }

}
