<?php
 return array (
  'database' => 
  array (
    'enabled' => true,
    'provider' => 'mysql',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
  ),
  'directory' => 
  array (
    'enabled' => true,
    'root' => 'C:\\webdev_test\\sites',
  ),
  'tld' => 'test',
  'web_server' => 
  array (
    'enabled' => true,
    'provider' => 'apache',
    'vhosts_enabled' => true,
    'vhosts_path' => 'C:\\webdev_test\\vhosts.conf',
  ),
  'hosts' => 
  array (
    'enabled' => true,
    'path' => 'C:\\webdev_test\\hosts',
  ),
  'options' => 
  array (
    'add' => 
    array (
      'create_database' => true,
      'create_directory' => true,
      'create_vhost' => true,
      'add_to_hosts' => true,
    ),
  ),
  'sites' => 
  array (
    'enabled' => true,
  ),
);