# Codeigniter 4 MongoDB Kitaplığı ve Ortak Model

## Kurulum

Mongo Driver ve Composer'a sisteminize kurmuş olmalısınız. Kurulum için şu bağlantıları izleyin:

<ul>
<li><a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos">Composer kurulumu</a></li>
<li><a href="https://www.php.net/manual/en/mongo.installation.php">Mongo sürücü kurulumu</a></li>
</ul>

Bir config dosyası oluşturmalısınız. Dosya oluşturduğunuzda, php etiketi için boşlukları silin. (app/Config/MongoConfig.php)

Uzak Bağlantı için srv değişkenine yorum satırındakini veri kullanılabilir veya boş bırakılabilir. authMechanism değişkeni bağlantı yapılırken gereken güvenlik protokolünü belirtmeniz için oluşturulmuştur.

<pre>
< ? php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class MongoConfig extends BaseConfig
{
    public $db = ''; //your database
    public $hostname = "";//127.0.0.1 if you use remote server you should change host address
    public $userName = "";
    public $password = "";
    public $prefix = '';
    public $port = "";//27017 if you use different port you should change port address
    public $srv='';//mongodb+srv
    //SCRAM-SHA-256 - SCRAM-SHA-1
    public $authMechanism="SCRAM-SHA-1";
}
</pre>

<code>composer require bertugfahriozer/ci4mongodblibrary</code>

manuel kurulum istiyorsanız aşağıdaki adımları takip edebilirsiniz.

**Codeigniter 4 projenize dosyaları taşıyabilirsiniz ve namespace değiştirmeniz gerekir =><br><br>app/Libraries/Mongo.php,<br>app/Config/MongoConfig.php,<br>app/Models/CommonModel.php.**

<a href="https://www.bynogame.com/destekle/bertugfahriozer-wwwyoutubecomchannelUCnw4Gyax5OAx6d4DtiNh_gw"><img src="https://bertugfahriozer.com/assets/images/gallery/BMC-logowordmark-Black.jpg" height="70"></a>
<hr>

# Codeigniter 4 MongoDB Library & Common Model

## Installation
You must have Mongo Driver and Composer. Follow these links for installation:

<ul>
<li><a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos">Composer installation</a></li>
<li><a href="https://www.php.net/manual/en/mongo.installation.php">Mongo driver installation</a></li>
</ul>

You must create a config file. When you create file, delete spaces for php tag. (app/Config/MongoConfig.php)

For Remote Connection, the data in the comment line can be used or left blank in the srv variable. The authMechanism variable is created to specify the required security protocol when connecting.

<pre>
< ? php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class MongoConfig extends BaseConfig
{
    public $db = ''; //your database
    public $hostname = "";//127.0.0.1 if you use remote server you should change host address
    public $userName = "";
    public $password = "";
    public $prefix = '';
    public $port = "";//27017 if you use different port you should change port address
    public $srv='';//mongodb+srv
    //SCRAM-SHA-256 - SCRAM-SHA-1
    public $authMechanism="SCRAM-SHA-1";
}
</pre>

<code>composer require bertugfahriozer/ci4mongodblibrary</code>

if do you want manual install you can follow these steps.

**You can move files in your Codeigniter 4 project and you must change namespaces => <br><br> app/Libraries/Mongo.php,<br>app/Config/MongoConfig.php,<br>app/Models/CommonModel.php.**

<a href="https://www.bynogame.com/destekle/bertugfahriozer-wwwyoutubecomchannelUCnw4Gyax5OAx6d4DtiNh_gw"><img src="https://bertugfahriozer.com/assets/images/gallery/BMC-logowordmark-Black.jpg" height="70"></a>
