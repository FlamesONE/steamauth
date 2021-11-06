<?php

namespace Flamesone\Steamauth;

use Exception;
use Flamesone\Steamauth\LightOpenID;

class Steam
{
    /**
     * @var string $domain
     */
    protected $domain = null;

    /**
     * @var LightOpenID
     */
    protected $openid;

    /**
     * @var string WEB Steam api key - https://steamcommunity.com/dev/apikey
     */
    protected $api_key;
    
    /**
     * @var bool Info from API KEY
     */
    protected $info = true;

    /**
     * @var bool Session listen
     */
    protected $session = false;

    /**
     * @var string steam url
     */
    public $steamurl = "https://steamcommunity.com/openid";
    
    /**
     * Constructor
     * 
     * @var string $site - LightOpenID domain
     * @var string $api_key - WEB API key
     * @var bool   $info - Avatar and name from steam api
     */
    public function __construct( string $site, string $api_key, bool $info = true )
    {
        $this->domain   = $site;
        $this->api      = $api_key;
        $this->info     = $info;
        $this->openid   = new LightOpenID( $this->domain );
    }

    /**
     * Auth function
     */
    public function Auth()
    {
        if ( !$this->openid->mode ) 
        {
            $this->openid->identity = $this->steamurl;

            $location = sprintf( "Location: %s", $this->openid->authUrl() );

            return header( $location );
        }
        elseif( $this->openid->mode == "cancel" )
            return false;
        else
            return true;
    }

    /**
     * Auth handle
     * 
     * @var callback $callback - user callback
     * 
     * @return callback|array
     */
    public function Handle( $callback = null )
    {
        if( $this->openid->mode && $this->openid->mode != "cancel" )
        {
            if( $this->openid->validate() )
            {
                $matches = [];

                $data = [];

                preg_match( "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $this->openid->identity, $matches );
      
                if( $this->info )
                {
                    $url = sprintf( "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s", $this->api_key, $matches[1] );
                    $data = json_decode( file_get_contents( $url ), true ); 
                }

                !$data["steamid"] && $data["steamid"] = $matches[1];

                $this->addToSession( $data );

                $callback && $callback( $data );

                return $data;
            }
            throw new Exception("Validate error");
        }
        throw new Exception("Open id mode is undefined");
    }

    /**
     * Set session listener
     */
    public function session( bool $state )
    {
        $this->session = $state;
        $state ? !$_SESSION && session_start() : $_SESSION && session_destroy();
    }

    /**
     * Logout from session
     */
    public function logout()
    {
        $this->session && ( $_SESSION && session_destroy() );
    }

    /**
     * Add values to session
     */
    protected function addToSession( array $data )
    {
        if( $this->session )
        {
            foreach( $data as $key => $val )
                $_SESSION[ $key ] = $val;
        }
    }
}