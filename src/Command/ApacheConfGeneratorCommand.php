<?php

namespace FrontController\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser as YamlParser;
use InvalidArgumentException;

class ApacheConfGeneratorCommand extends Command
{
    private $basePath;
    private $webRoot;
    private $hosts = array();

    protected function configure()
    {
        $this
            ->setName('frontcontroller:apacheconf')
            ->setDescription('Generate Apache configurations')
            ->addArgument(
                'webroot',
                InputArgument::REQUIRED,
                'The webroot. e.g. /var/www'
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to the websites root directory.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->basePath = $input->getArgument('path');
        $this->webRoot = $input->getArgument('webroot');

        if (!is_dir($this->basePath)) {
            throw new InvalidArgumentException('Invalid path or path is not directory');
        }

        $this->getHosts();
        // $content = $this->getConfigContent();
        // $targetPath = $this->getTargetPath();

        if (false === file_put_contents($this->getTargetPath(), $this->getConfigContent())) {
            $output->writeln('<error>No permission to write config, run with sudo maybe.</error>');
            return;
        }

        $output->writeln('<info>Done! Please include '.$this->getTargetPath().' in your apache conf.</info>');
    }

    private function getHosts()
    {
        $files = glob($this->basePath.'*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $parser = new YamlParser();
                $config = $parser->parse(file_get_contents($file.'/frontcontroller.yml'));
                if (isset($config['host'])) {
                    $this->hosts []= array('path' => $file, 'host' => $config['host']);
                }
            }
        }

        return $this->hosts;
    }

    private function getConfigContent()
    {
        $lb = "\n";
        $o = '';
        foreach ($this->hosts as $host) {
            $o .= $lb.'<VirtualHost *:80>
    DocumentRoot "'.$this->webRoot.'"
    ServerName '.$host['host'].'
    SetEnv FRONTCONTROLLER_BASEPATH "'.$host['path'].'"
    <Directory '.$this->webRoot.'>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>';
        }

        return $o;
    }

    private function getTargetPath()
    {
        switch (php_uname('s')) {
            case 'Darwin':
                $o = '/etc/apache2/extra/httpd-frontcontroller.conf';
                break;
            default:
                $o = '/etc/apache2/sites-available/frontcontroller.conf';
                break;
        }

        return $o;
    }
}
