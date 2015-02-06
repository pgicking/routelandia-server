<?php

namespace Routelandia;


class Util
{
    /** Check if array has a next element
     *
     * check if an array has a next element
     *
     * @param array $array
     * @return bool
     */
    static function has_next($array)
    {
        if (is_array($array)) {
            if (next($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * dbug (mixed $expression [, mixed $expression [, $... ]])
     * Author : dcz
     * Feel free to use as you wish at your own risk ;-)
     */
    public function dbug() {
        static $output = '', $doc_root;
        $args = func_get_args();
        if (!empty($args) && $args[0] === 'print') {
            $_output = $output;
            $output = '';
            return $_output;
        }
        // do not repeat the obvious (matter of taste)
        if (!isset($doc_root)) {
            $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        }
        $backtrace = debug_backtrace();
        // you may want not to htmlspecialchars here
        $line = htmlspecialchars($backtrace[0]['line']);
        $file = htmlspecialchars(str_replace(array('\\', $doc_root), array('/', ''), $backtrace[0]['file']));
        $class = !empty($backtrace[1]['class']) ? htmlspecialchars($backtrace[1]['class']) . '::' : '';
        $function = !empty($backtrace[1]['function']) ? htmlspecialchars($backtrace[1]['function']) . '() ' : '';
        $output .= "<b>$class$function =&gt;$file #$line</b><pre>";
        ob_start();
        foreach ($args as $arg) {
            var_dump($arg);
        }
        $output .= htmlspecialchars(ob_get_contents(), ENT_COMPAT, 'UTF-8');
        ob_end_clean();
        $output .= '</pre>';
    }
}
