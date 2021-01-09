<?php namespace PetterThowsen\Webdev;

use Exception;
use Illuminate\Config\Repository;

class Console extends \Symfony\Component\Console\Application
{
    
    private static $_console;

    private static $defaultConfig = [
        'database' => [
            'enabled' => false,
            'provider' => 'mysql',
            'user' => 'root',
            'password' => '',
            'host' => '',
        ],
        'directory' => [
            'enabled' => true,
            'root' => '/var/www',
        ],
        'tld' => 'test',
        'web_server' => [
            'enabled' => false,
            'provider' => 'apache',
            'vhosts_enabled' => false,
            'vhosts_path' => null,
        ],
        'hosts' => [
            'enabled' => false,
            'path' => null,
        ],
        'options' => [
            'add' => [
                'create_database' => false,
                'create_directory' => false,
                'create_vhost' => false,
            ]
        ],
    ];

    public $config;

    public static function getInstance() : Console {
        if ( ! isset(static::$_console)) static::$_console = new static();
        return static::$_console;
    }

    public static function getConfig() : Repository
    {
        return static::getInstance()->config;
    }

    private function __construct() {
        parent::__construct();

        $this->config = new Repository(static::$defaultConfig);
        
        if (WEBDEV_CONFIGURED) {
            $this->config->set(include(WEBDEV_CONFIG_FILE));
            $this->addCommands([
                new SetupCommand(),
                new AddCommand(),
            ]);
        }else {
            $this->addCommands([
                new SetupCommand(),
            ]);
        }
        
    }
/**
	 * @param string $project
	 * @throws Exception
     * @return string
	 */
	public function createDirectory($name)
	{
		$root = $this->config->get('directory.root');

		if (! is_dir($root)) {
			throw new \Exception('The directory.root "' .$root .'" does not exist!');
		}

		$folder = $root .DIRECTORY_SEPARATOR .$name;

		if (is_dir($folder) || mkdir($folder, 0775)) {
			return $folder;
		}

		return $folder;
	}

	/**
	 * @param string $file the virtualhosts.conf file
	 * @param string $name project name
	 * @param string $directory directory of the web root
	 * @return string the virtualhost code generated
	 * @throws Exception if the virtualhosts.conf file is missing.
	 */
	public function appendVirtualHostsDirective($name, $tld, $directory)
	{
        $file = $this->config->get('web_server.vhosts_path');
		if ( ! file_exists($file)) {
			throw new Exception("The virtualhosts file '" .$file . "' is missing!");
		}

        $template = \file_get_contents(WEBDEV_VHOST_TEMPLATE_FILE);

        $matches = [];
        \preg_match_all('/\${([a-zA-Z-_]+)}/', $template, $matches);

        # setup variables
        $variables = [
            'name' => $name,
            'tld' => $tld,
            'directory' => $directory,
        ];

        foreach($matches[0] as $key => $match) {
            $variableName = $matches[1][$key];
            $template = str_replace($match, $variables[$variableName], $template);
        }

		$h = fopen($file, 'a');
		fwrite($h, "\n\n$template");
		fclose($h);

		return $template;
	}

	/**
	 * @param string $project
	 */
	public function createDatabase($name, $provider = 'mysql')
	{
		# get the provider & credentials
		$credentials = $this->config->get('database' .$provider);

		if ($provider == 'mysql') {
			$pdo = new \PDO('mysql:host=' .$credentials['host'], $credentials['user'], $credentials['password']);
			if ($pdo->exec("CREATE DATABASE `$name`;") !== false) {
                return true;
			}
		}

		return false;
	}

	public function addHostsFile($name, $tld, $subdomains = ['www'])
	{
		$h = fopen($this->config->get('hosts.path'), 'a');
        
        fwrite($h, "\n\n#begin_wedev:$name.$tld");
		fwrite($h, "\n127.0.0.1    " .$name .'.' .$tld);
		foreach($subdomains as $subdomain) {
			fwrite($h, "\n127.0.0.1 $subdomain.$name.$tld");
		}
		fwrite($h, "\n#end_wedev:$name");

        return true;
	}

    /**
	 * Saves the current configuration to WEBDEV_CONFIG_FILE, like this: "<?php return array(...);".
	 */
	public function save()
	{
		$h = fopen(WEBDEV_CONFIG_FILE, 'w');
		fwrite($h, "<?php\n return " .var_export($this->config->all(), true) .';');
		fclose($h);
	}

    public static function isWindows() : bool
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			return true;
		else
			return false;
	}


}