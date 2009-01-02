<?php
class Html
{
    function start($indent)
    {
        echo str_repeat(INDENT_STRING, $indent);
    }

    function show($html)
    {
        echo $html;
    }

    function end()
    {
        echo "\n";
    }

    /***
     * Print all arguments on the same line with the specified indent.
     ***/
    function showLine($indent)
    {
        echo str_repeat(INDENT_STRING, $indent);
        $size = func_num_args();
        for ($i = 1; $i < $size; $i++)
        {
            echo func_get_arg($i);
        }
        echo "\n";
    }

    function showLines($indent)
    {
        $size = func_num_args();
        for ($i = 1; $i < $size; $i++)
        {
            $block = func_get_arg($i);
            $lines = explode("\n", $block);
            $length = count($lines);
            for ($j = 0; $j < $length; $j++)
            {
                echo str_repeat(INDENT_STRING, $indent), $lines[$j], "\n";
            }
        }
    }

    /***
     * Print all arguments at separate lines, each with the specified indent.
     ***/
    function showBlock($indent)
    {
        $size = func_num_args();
        for ($i = 1; $i < $size; $i++)
        {
            echo str_repeat(INDENT_STRING, $indent), func_get_arg($i), "\n";
        }
    }
}
?>
