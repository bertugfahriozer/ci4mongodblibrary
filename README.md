# Codeigniter 4 MongoDB Library & Common Model

## Installation
You must have Mongo Driver and Composer. Follow these links for installation:
<ul>
<li><a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos">Composer installation</a></li>
<li><a href="https://www.php.net/manual/en/mongo.installation.php">Mongo driver installation</a></li>
</ul>

You must create a config file. When you create file, delete spaces for php tag. (app/Config/MongoConfig.php)
<pre>
< ? php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class MongoConfig extends BaseConfig
{
    public $db = "";//your database
    public $hostname = '127.0.0.1';//if you use remote server you should change host address
    public $userName = "root";
    public $password = "";
    public $port = 27017;//if you use different port you should change port address
}
</pre>

if do you want manual install you can follow these steps.

**You can move files in your Codeigniter 4 project and you must change namespaces => <br><br> app/Libraries/Mongo.php,<br>app/Config/MongoConfig.php,<br>app/Models/CommonModel.php.**
