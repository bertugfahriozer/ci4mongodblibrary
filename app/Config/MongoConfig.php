<?php namespace ci4mongodblibrary\Config;

use CodeIgniter\Config\BaseConfig;

class MongoConfig extends BaseConfig
{
    public $db = ""; //your database
    public $hostname = '127.0.0.1'; //if you use remote server you should change host address
    public $userName = "";
    public $password = "";
    public $prefix = "";
    public $port = 27017; //if you use different port you should change port address
}
