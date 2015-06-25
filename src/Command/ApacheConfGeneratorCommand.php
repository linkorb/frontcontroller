<?php

namespace FrontController\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
                'path',
                InputArgument::REQUIRED,
                'Path to the websites root directory.'
            )->addArgument(
                'webroot',
                InputArgument::OPTIONAL,
                'The webroot. e.g. /var/www'
            )
            ->addOption(
                'hackhosts',
                null,
                InputOption::VALUE_NONE,
                'Use this option to hack the local hosts file so that the hosts in hosts.yml becomes localhost'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setBasePath($input->getArgument('path'));
        $this->setWebRoot($input->getArgument('webroot'));
        $hackhosts = $input->getOption('hackhosts');

        if (!is_dir($this->basePath)) {
            $output->writeln('<error>Invalid path or path is not directory'.$this->basePath.'</error>');
            return;
        }

        $this->getHosts();
        if (count($this->hosts) == 0) {
            $output->writeln(
                '<error>No host configuration found.
                Please put "host: www.example.com" in your frontcontroller.yml</error>'
            );
            return;
        }

        if (false === file_put_contents($this->getTargetPath(), $this->getConfigContent())) {
            $output->writeln('<error>No permission to write config, run with sudo maybe.</error>');
            return;
        }

        if ($hackhosts) {
            $this->hackHosts($output);
        }

        $output->writeln($this->reloadApache());

        $output->writeln('<info>Done! Please include '.$this->getTargetPath().' in your apache conf.</info>');
    }

    private function setBasePath($path)
    {
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }
        $this->basePath = $path;
    }

    private function setWebRoot($webroot)
    {
        $realPath = __DIR__.'/../../web';
        $this->webRoot = $webroot;
        if ($this->webRoot) {
            if (!file_exists($webroot)) {
                symlink($realPath, $webroot);
            }
        } else {
            $this->webRoot = $realPath;
        }
    }

    private function getHosts()
    {
        $parser = new YamlParser();
        $files = glob($this->basePath.'*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                if (file_exists($file.'/hosts.yml')) {
                    $config = $parser->parse(file_get_contents($file.'/hosts.yml'));
                    if (isset($config['host'])) {
                        $host = array('path' => realpath($file), 'alias' => array());
                        foreach ($config['host'] as $h) {
                            if (!isset($host['host'])) {
                                $host['host'] = $h;
                            } else {
                                $host['alias'] []= $h;
                            }
                        }
                        $this->hosts []= $host;
                    }
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
    ServerName '.$host['host'].$this->insertAlias($host).'
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

    private function insertAlias($host)
    {
        $o = '';
        if (count($host['alias']) > 0) {
            $o .= '
    ServerAlias ';
            foreach ($host['alias'] as $a) {
                $o .= $a.' ';
            }
            $o = rtrim($o);
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

    private function reloadApache()
    {
        switch (php_uname('s')) {
            case 'Darwin':
                $o = 'apachectl restart';
                break;
            default:
                $o = '/etc/init.d/apache2 reload';
                break;
        }

        return exec($o);
    }

    private function hackHosts(OutputInterface $output)
    {
        $hosts = [];
        foreach ($this->hosts as $host) {
            $hosts []= $host['host'];
            foreach ((array) $host['alias'] as $alias) {
                $hosts []= $alias;
            }
        }

        if (!$hosts) {
            return false;
        }

        $path = '/etc/hosts';
        $o = '';
        $file = file_get_contents($path);
        foreach ($hosts as $host) {
            if (stripos($file, "\t".$host) === false) {
                $o .= "\n127.0.0.1\t$host";
            }
        }

        if ($o) {
            file_put_contents($path, $o, FILE_APPEND);
            $output->writeln('<info>Hosts file hacked: '.$o.'</info>');
            return true;
        }

        return false;
    }
}
