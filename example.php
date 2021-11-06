<?php

use Flamesone\Steamauth\Steam;

$data = [
    "url" => "http://example.com",
    "key" => "KEY"
];

$steam = new Steam( $data["url"], $data["key"] );

try
{
    !$_SESSION && $steam->Auth();
}
catch( Exception $e )
{
    header("/");
}

$steam->session( true );

$steam->Handle( function($data)
{
    header("/");
});

print_r( $_SESSION );