<?php
/**
 * Little Lonely Bouncer
 */
namespace LittleLonelyBouncer\Exceptions;

class ErrorsList
{

    public static function pages(int $i) : array
    {

        $errors = [
            '-1' => 'Table creation failure.'
        ];

        $result = [];

        if (isset($errors[(string)$i])) $result = [
            'code' => $i,
            'message' => $errors[(string)$i]
        ];

        return $result;

    }

}
