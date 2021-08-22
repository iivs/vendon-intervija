<?php declare(strict_types = 1);

/**
 * A file parser class to read controller classes, find class names, public functions and variables. It cannot determine
 * if variables are required or not.
 */
final class FileParser
{

    /**
     * Parse the content given by file_get_contents(). Uses tokenizer.
     *
     * @param string $content   File contents
     * @param int    $pos       Position from where to start parsing the content.
     *
     * @return array    Example output:
     *
     *  'TestsController' => [
     *      'functions' => [
     *          'index' => [
     *              'variables => []
     *          ],
     *          'questions' => [
     *              'variables' => [
     *                  $test_id,
     *                  $question_idx
     *              ]
     *          ],
     *          'results' => [
     *              'variables' => []
     *          ],
     *          'errors' => [
     *              'variables' => []
     *          ]
     *      ]
     *  ]
     */
    public function parse(string $content, $pos = 0): array
    {
        // This will store the output result.
        $result = [];

        // Current token, class, function and variable.
        $token = '';
        $class = '';
        $function = '';
        $variable = '';

        // Split contents into tokens and check them one by one.
        $tokens = token_get_all($content);

        while (isset($tokens[$pos])) {
            if (is_array($tokens[$pos])) {
                if ($tokens[$pos][0] === T_EXTENDS) {
                    // Extends token found. Set active token as class T_EXTENDS.
                    $token = T_EXTENDS;
                }

                if ($tokens[$pos][0] === T_CLASS && $token !== T_EXTENDS) {
                    // Main class token found. Not the extended class name. Set active token as class T_CLASS.
                    $token = T_CLASS;
                }

                if ($tokens[$pos][0] === T_PROTECTED) {
                    // Protected token found. Set active token as class T_PROTECTED.
                    $token = T_PROTECTED;
                }

                if ($tokens[$pos][0] === T_PUBLIC) {
                    // Public token found. Set active token as class T_PUBLIC.
                    $token = T_PUBLIC;
                }

                if ($tokens[$pos][0] === T_FUNCTION && $token === T_PUBLIC) {
                    // A function token was wound. But only set active token as class T_FUNCTION if it is public.
                    $token = T_FUNCTION;
                }

                if ($tokens[$pos][0] === T_STRING) {
                    // A string token was found. We need only class and funtion names.
                    switch ($token) {
                        case T_CLASS:
                            // String belongs to class. So this is the class name in file.
                            $class = $tokens[$pos][1];

                            // Prepare the functions that will be in this class.
                            $result[$class] = [
                                'functions' => []
                            ];
                            break;

                        case T_FUNCTION:
                            // String belongs to function. So this is a public function name.
                            if ($tokens[$pos][1] === 'null' || $tokens[$pos][1] === 'false'
                                    || $tokens[$pos][1] === 'true' || $tokens[$pos][1] === '__construct') {
                                // Skip strings that are not function names.
                            }
                            else {
                                $function = $tokens[$pos][1];
                                $result[$class]['functions'][$function] = [
                                    'variables' => []
                                ];
                            }
                            break;
                    }
                }

                if ($tokens[$pos][0] === T_VARIABLE) {
                    // Found a variable token, but we only need public function variables.
                    if ($token === '(' || $token === ',') {
                        // Determine if this variable belongs to function, first in n-th parameter.
                        $variable = $tokens[$pos][1];
                        $result[$class]['functions'][$function]['variables'][] = $variable;
                        $token = T_FUNCTION;
                    }
                }
            }
            else {
                /*
                 * Not all tokens are arrays. Here we encounter parentheris and commas. We only need ones that belong
                 * to a function. Variables can have strings before them, equal signs and closing parenthesis.
                 * Unfortunately the parser does not support $var = array(), because these parenthesis mess up the
                 * structure.
                 */
                switch ($token) {
                    case T_FUNCTION:
                        switch ($tokens[$pos]) {
                            case '(':
                                $token = '(';
                                break;

                            case ',':
                                $token = ',';
                                break;

                            case '=':
                                $token = T_FUNCTION;
                                break;

                            case ')':
                                $token = ')';
                                break;
                        }
                        break;

                    case '(':
                        switch ($tokens[$pos]) {
                            case ',':
                                $token = ',';
                                break;

                            case ')':
                                $token = ')';
                                break;
                        }
                        break;
                }
            }

            $pos++;
        }

        return $result;
    }
}
