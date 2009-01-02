<?php
class History
{
    var $urls;

    function History()
    {
        $this->clear();
    }

    function pop()
    {
        return array_pop($this->urls);
    }

    function push($url)
    {
        array_push($this->urls, $url);
    }

    function clear()
    {
        $this->urls = array();
    }
}
?>