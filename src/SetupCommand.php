<?php namespace PetterThowsen\Webdev;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SetupCommand extends Command
{

    protected static $defaultName = "setup";

    protected $supportedDatabases = [
        'mysql'
    ];

    protected function configure()
    {
        $this
            ->setDescription("Setup WebDev config.")
            ->setHelp("Setup webdev config, like default options and various paths.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Console::getConfig();
        
        ###########################
        #------- DATABASE --------#
        #-------------------------#
        $database_enabled = $this->confirm('Can webdev create databases for you?', false);
        if ($database_enabled) {
            $config->set('database.enabled', true);

            $db_provider = $this->choice('What database do you use?', ['mysql', 'mariadb', 'nosql', 'postgresql'], 'mysql');

            if ( ! \in_array($db_provider, $this->supportedDatabases)) {
                $output->writeln($db_provider .' is not yet supported');
            }

            $db_host = $this->ask('database.host = ', 'localhost');
            $db_user = $this->ask('database.user = ', 'root');
            $db_password = $this->ask('database.password = ', '');

            $config->set('database.host', $db_host);
            $config->set('database.user', $db_user);
            $config->set('database.password', $db_password);

            $config->set('options.add.create_database', $this->confirm('Create new databases by default?', false));
        }else {
            $config->set('database.enabled', false);
        }
        
        ###########################
        #---- SITE DIRECTORY -----#
        #-------------------------#
        $sites_enabled = $this->confirm('Can webdev create the website directory for you?', false);
        $config->set('sites.enabled', $sites_enabled);
        if ($sites_enabled) {
            $directory_root = $this->ask('Where should I put the website directories I create for you? (for example /var/www)');
            $config->set('directory.root', $directory_root);

            $config->set('options.add.create_directory', $this->confirm('When adding new sites, create the website directory by default? ', false));
        }
        
        # WEB SERVER AND VHOSTS
        $web_server = $this->choice('What web server do you use?', ['apache', 'none'], 'none');
        if ($web_server == 'apache') {
            $config->set('web_server.enabled', true);
            $vhosts_enabled = $this->confirm('Can webdev manage apache vhosts?', false);
            $config->set('web_server.vhosts_enabled', $vhosts_enabled);
            if ($vhosts_enabled) {
                $vhosts_path = $this->ask('Where is your vhosts.conf file? ');
                $config->set('web_server.vhosts_path', $vhosts_path);
                
                $config->set('options.add.create_vhost', $this->confirm('When adding new sites, create the vhost by default? ', true));
            }
        }

        $config->set('tld', $this->ask('What do you use for top-level-domain? ', 'test'));

        if ($this->confirm('add site to hosts file?')) {
            $config->set('hosts.enabled', true);
            $hostsfile = '/etc/hosts';

            if (Console::isWindows()) {
                $hostsfile = 'C:\\Windows\\System32\\drivers\\etc\\hosts';
            }
            
            if ( ! $this->confirm('Is this your hosts file?')) {
                $hostsfile = $this->ask('Ok, where is it?');
            }

            $config->set('hosts.path', $hostsfile);

            $config->set('options.add.add_to_hosts', $this->confirm('When adding new sites, add the site to hosts file by default?', true));
        }

        $output->writeln("Saving configuration to " .WEBDEV_CONFIG_FILE ."...");
        Console::getInstance()->save();

        $output->writeln("Done!");

        return Command::SUCCESS;
    }

}