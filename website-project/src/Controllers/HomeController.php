<?php

class HomeController
{
    public function index()
    {
        View::render('home', ['title' => 'House Hunting - Your Dream Home Awaits']);
    }
}

