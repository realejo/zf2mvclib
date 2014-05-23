<?php
/**
 * Gerenciador de cache utilizado pelo App_Model
 *
 * Ele cria automaticamente a pasta de cache, dentro de data/cache, baseado no nome da classe
 *
 * @author     Realejo
 * @version    $Id: Cache.php 54 2014-03-21 17:16:12Z rodrigo $
 * @copyright  Copyright (c) 2012 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Model;

use Zend\Cache\StorageFactory;

class Cache
{

    /**
     *
     * @var Zend\Cache\StorageFactory
     */
    private $_cache;

     /**
      * Configura o cache
      *
      * @return Zend_Cache_Frontend
      */
     static public function getFrontend($class = '')
     {
         // Configura o cache
         $this->_cache = StorageFactory::factory(array(
                         'adapter' => array(
                                 'name' => 'filesystem',
                                 'options' => array(
                                         'cache_dir' => self::getCachePath($class),
                                         'namespace' => $class
                                 ),
                         ),
                         'plugins' => array(
                                 // Don't throw exceptions on cache errors
                                 'exception_handler' => array(
                                         'throw_exceptions' => false
                                 ),
                                 'Serializer'
                         ),
                         'options' => array(
                                 'ttl' => 86400
                         )
                 ));

         return $this->_cache;
     }

     /**
      * Apaga o cache de consultas do model
      */
     static public function clean()
     {
         // Apaga o cache
         self::getFrontend()->clean();
     }

     /**
      * Retorna a pasta raiz de todos os caches
      *
      * @return string
      */
     static public function getCacheRoot()
     {
         // Verifica se a pasta de cache existe
         if (defined('APPLICATION_DATA') === false) {
             throw new \Exception('A pasta raiz do data não está definido em APPLICATION_DATA em App_Model_Cache::getCacheRoot()');
         }

         $cachePath = APPLICATION_DATA . '/cache';

         // Verifica se a pasta do cache existe
         if (!file_exists($cachePath)) {
             $oldumask = umask(0);
             mkdir($cachePath, 0777, true); // or even 01777 so you get the sticky bit set
             umask($oldumask);
         }

         // retorna a pasta raiz do cache
         return realpath($cachePath);
     }

     /**
      * Retorna a pasta de cache para o model baseado no nome da classe
      * Se a pasta não existir ela será criada
      *
      * @param string $class Nome da classe a ser usada
      *
      * @return string
      */
     static public function getCachePath($class = '')
     {
         // Define a pasta de cache
         $cachePath = self::getCacheRoot() . '/' . str_replace('_', '/', strtolower($class));

         // Verifica se a pasta do cache existe
         if (!file_exists($cachePath)) {
             $oldumask = umask(0);
             mkdir($cachePath, 0777, true); // or even 01777 so you get the sticky bit set
             umask($oldumask);
         }

         // Retorna a pasta de cache
         return realpath($cachePath);
     }

     /**
      * Ignora o backend e apaga os arquivos do cache. inclui as subpastas.
      * Serão removio apenas os arquivos de cache e não as pastas
      *
      * @param string $path
      */
     static public function completeCleanUp($path)
     {
         if (is_dir($path)) {
             $results = scandir($path);
             foreach ($results as $result) {
                 if ($result === '.' or $result === '..') continue;

                 if (is_file($path . '/' . $result)) {
                     unlink($path . '/' . $result);
                 }

                 if (is_dir($path . '/' . $result)) {
                     self::completeCleanUp($path . '/' . $result);
                 }
             }
         }
     }
}
