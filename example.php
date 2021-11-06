<?php

use Flamesone\Steamauth\Steam;

$data = [
    "url" => "http://example.com",
    "key" => "KEY"
];

$steam = new Steam( $data["url"], $data["key"] );

if( $steam->Auth() )
{
    $steam->Handle( function($data)
    {
        print_r( $data );
    });
}