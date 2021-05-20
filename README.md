# Codeigniter 4 MongoDB Library & Common Model

## Installation
You must have Mongo Driver and Composer. Follow these links for installation:
<ul>
<li><a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos">Composer installation</a></li>
<li><a href="https://www.php.net/manual/en/mongo.installation.php">Mongo driver installation</a></li>
</ul>

if do you want manual install you can follow these steps.

**You can move files in your Codeigniter 4 project => <br><br> app/Libraries/Mongo.php,<br>app/Config/MongoConfig.php,<br>app/Models/CommonModel.php.**

You must update config file (app/Config/MongoConfig.php) 
<pre>
private $db = "";//your database
private $hostname = '127.0.0.1';//if you use remote server you should change host address
private $userName = "root";
private $password = "";
private $port = 27017;//if you use different port you should change port address
</pre>
