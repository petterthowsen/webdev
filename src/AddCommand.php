<?php namespace PetterThowsen\Webdev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddCommand extends Command
{

    protected static $defaultName = "add";

    protected function configure()
    {
        $this
            ->setDescription("Add a new site.")
            ->setHelp("Add a new site. This will create a directory, add virtualhosts to apache and edit your hosts file.")
            ->addArgument("name", InputArgument::REQUIRED, "Name of the site");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $console = Console::getInstance();
        $config = Console::getConfig();

        $name = $input->getArgument("name");
        $tld = $config->get('tld');
        $output->writeln("Creating site " . $name . "...");
        
        if ($config->get('options.add.create_directory')) {
            $dir = $console->createDirectory($name);
            $output->writeln('directory created: ' . $dir);
        }

        if ($config->get('options.add.add_to_hosts')) {
            $console->addHostsFile($name, $tld);
            $output->writeln("$name.$tld added to hosts file. Remember to flush your dns cache.");
        }

        if ($config->get('options.add.create_vhost')) {
            $dir = $config->get('directory.root') .DIRECTORY_SEPARATOR .$name;
            $console->appendVirtualHostsDirective($name, $tld, $dir);
        }

        return Command::SUCCESS;
    }

}