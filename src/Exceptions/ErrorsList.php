<?php
/**
 * Little Lonely Bouncer
 */
namespace LittleLonelyBouncer\Exceptions;

class ErrorsList
{

    /**
     * LittleLonelyBouncer\Providers\Pages errors.
     * 
     * @param int $i
     * Error code.
     * 
     * @return array
     * Associative array with "code" and "message" keys.
     */
    public static function pages(int $i) : array
    {

        $errors = [
            '-1' => 'Table creation failure.',
            '-2' => 'Value insertion failure.',
            '-3' => 'Page ID cannot be lesser than 1.',
            '-4' => 'Page not exist in the table.',
            '-5' => 'Page URI cannot be empty.',
            '-6' => 'Page addition failure.',
            '-7' => 'Page cannot be added by this way.'
        ];

        $result = [];

        if (isset($errors[(string)$i])) $result = [
            'code' => $i,
            'message' => $errors[(string)$i]
        ];

        return $result;

    }

}
