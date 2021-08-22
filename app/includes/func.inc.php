<?php declare(strict_types = 1);

/**
 * Change the array key to one of child array values. Exaple usage:
 * 
 * $in = [
 *      0 => [
 *          'name' => 'Title one',
 *          'article_id' => 25,
 *      ],
 *      1 => [
 *          'name' => 'Title two',
 *          'article_id' => 47,
 *      ]
 * ];
 * 
 * $out = arrayAddKey($in, 'article_id')
 * 
 * Result $out:
 *  [
 *      25 => [
 *          'name' => 'Title one',
 *          'article_id' => 25,
 *      ],
 *      47 => [
 *          'name' => 'Title two',
 *          'article_id' => 47,
 *      ]
 * ]
 * 
 * @param array  $in        Input array.
 * @param string $field     Field name if child array.,
 */
function arrayAddKey(array $in, string $field) {
    $out = [];

    array_walk($in, function($array, $idx, $key) use (&$out) {
        $new_idx = $array[$key];
        $out[$new_idx] = $array;
    }, $field);

    return $out;
}

/**
 * Function to translate the string. Actual translations of string are not yet implemented.
 */
function __(string $string) {
    $arguments = array_slice(func_get_args(), 1);

    return vsprintf($string, $arguments);
}
