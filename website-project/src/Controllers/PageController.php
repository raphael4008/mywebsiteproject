<?php

class PageController
{
    public function show($page)
    {
        View::render($page, ['title' => ucwords(str_replace('-', ' ', $page))]);
    }
}
