<?php
/**
 * Little Lonely Bouncer
 */
namespace LittleLonelyBouncer;

final class Main
{

    private $path;
    private $url;
    private $wpdb;

    public function __construct(string $path, string $url)
    {
        
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->path = $path;
        $this->url = $url;

    }

}
