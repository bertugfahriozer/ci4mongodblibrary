<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class SimpleConfig extends BaseConfig
{
    public $dbInfo = [];

    public function __construct()
    {
        $this->dbInfo = [
            'default' => (object)[
                'db' => '', //your database
                'hostname' => "",//127.0.0.1 if you use remote server you should change host address
                'userName' => "",
                'password' => "",
                'prefix' => '',
                'port' => "",//27017 if you use different port you should change port address
                'srv' => 'mongodb',//mongodb+srv
                //SCRAM-SHA-256 - SCRAM-SHA-1
                'authMechanism' => "SCRAM-SHA-1",
                'db_debug' => TRUE,
                'write_concerns' => (int)1,
                'journal' => TRUE,
                'read_preference' => 'primary',
                'read_concern' => 'local', //'local', 'majority' or 'linearizable'
                'ca_file'=>[]//['ca_file' => '/usr/local/etc/openssl/cert.pem']
            ]
        ];
    }
}
