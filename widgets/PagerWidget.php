<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class PagerWidget extends Widget
{
    var $url;
    var $parameter;
    var $index;
    var $size;
    var $first;
    var $last;

    function PagerWidget($id, &$form)
    {
        $this->Widget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('first_page_visible'   , '|&lt;&lt;');
        $this->setProperty('first_page_hidden'    , '|&lt;&lt;');
        $this->setProperty('previous_list_visible', '&lt;&lt;');
        $this->setProperty('previous_list_hidden' , '&lt;&lt;');
        $this->setProperty('previous_page_visible', '&lt;');
        $this->setProperty('previous_page_hidden' , '&lt;');
        $this->setProperty('next_page_visible'    , '&gt;');
        $this->setProperty('next_page_hidden'     , '&gt;');
        $this->setProperty('next_list_visible'    , '&gt;&gt;');
        $this->setProperty('next_list_hidden'     , '&gt;&gt;');
        $this->setProperty('last_page_visible'    , '&gt;&gt;|');
        $this->setProperty('last_page_hidden'     , '&gt;&gt;|');
        $this->setProperty('list_size'            , 10);
        $this->setProperty('auto_hide'            , true);
    }

    function buildWidget()
    {
        $window =& $this->getOwner();
        $this->parameter = $window->getObjectId() . '_index';
    }

    function showWidget($indent)
    {
        $window          =& $this->getOwner();
        $page            =& $window->getPage();
        $this->url       =& new Url($page->getUrl());
        $length          =  $this->getProperty('list_size');
        $this->first     =  floor(($this->index) / $length) * $length + 1;
        $this->last      =  $this->size < $this->first + $length
            ? $this->size + 1
            : $this->first + $length;
        if ($this->getProperty('auto_hide') && $this->size < 2)
        {
            return;
        }
        $this->showButtons($indent);
        Html::showLine($indent, '<br/>');
        $this->showList($indent);
    }

    function showButtons($indent)
    {
        $length = $this->getProperty('list_size');
        $this->showButton(
            $indent,
            'first_page',
            0,
            $this->index > 0
        );
        $this->showButton(
            $indent,
            'previous_list',
            $this->index - $length,
            $this->first > $length
        );
        $this->showButton(
            $indent,
            'previous_page',
            $this->index - 1,
            $this->index > 0
        );
        $this->showButton(
            $indent,
            'next_page',
            $this->index + 1,
            $this->index < $this->size - 1
        );
        $this->showButton(
            $indent,
            'next_list',
            min($this->index + $length, $this->size - 1),
            $this->last < $this->size
        );
        $this->showButton(
            $indent,
            'last_page',
            $this->size - 1,
            $this->index < $this->size - 1
        );
    }

    function showButton($indent, $name, $index, $isVisible)
    {
        if (!$isVisible)
        {
            Html::showLine($indent, $this->getProperty($name . '_hidden'));
            return;
        }
        $this->url->setParameter($this->parameter, $index);
        Html::showLine(
            $indent,
            $this->url->getLink(
                $this->getProperty($name . '_visible'),
                $this->getProperty('css')
            )
        );
    }

    function showList($indent)
    {
        for ($i = $this->first; $i < $this->last; $i++)
        {
            if ($i - 1 == $this->index)
            {
                Html::showLine($indent, '<b>' . $i . '</b>');
            }
            else
            {
                $this->url->setParameter($this->parameter, $i - 1);
                Html::showLine(
                    $indent, $this->url->getLink($i, $this->getProperty('css'))
                );
            }
        }
    }

    function setSize($size)
    {
        $this->size = $size;
    }

    function setIndex($index)
    {
        $this->index = $index;
    }
}
?>
