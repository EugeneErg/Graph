<?php declare(strict_types=1);
namespace EugeneErg\Graph\Errors;

class ValueError extends \ValueError
{
    //array_rand(): Argument #1 ($array) cannot be empty"
    //array_rand(): Argument #2 ($num) must be between 1 and the number of elements in argument #1 ($array)
    //json_decode(): Argument #3 ($depth) must be greater than 0
    //strpos(): Argument #3 ($offset) must be contained in argument #1 ($haystack)
    //mb_substitute_character(): Argument #1 ($substitute_character) must be "none", "long", "entity" or a valid codepoint
    //curl_setopt(): Argument #2 ($option) is not a valid cURL option
    //mb_convert_encoding(): Argument #3 ($from_encoding) contains invalid encoding "none" src/XF/Http/Metadata.php:200
    //version_compare(): Argument #3 ($operator) must be a valid comparison operator
}
