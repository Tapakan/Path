<?php
/**
 * Path helper class.
 * @package     Tapakan\Path
 * @version     1.0.1
 * @license     http://mit-license.org/
 * @author      Tapakan https://github.com/Tapakan
 * @coder       Alexander Oganov <t_tapak@yahoo.com>
 */

namespace Tapakan\Path;

use InvalidArgumentException;
use Tapakan\Path\Exception\PathException;

/**
 * Class Path
 * @package Tapakan\Path
 */
class Path
{
    /**
     * Path to document root.
     * @type string
     */
    protected static $root;
    
    /**
     * You can get created instance by Identifier.
     * @type array Array of instances
     */
    protected static $instances = [];
    
    /**
     * Key of path alias must start with symbol '@'.
     * @type array Array of paths aliases.
     */
    protected $aliases = [];
    
    /**
     * If identifier exists it will be return instance,
     * if not new instance will create.
     *
     * @param mixed $id Identifier of instance.
     *
     * @return Path
     */
    public static function getInstance($id = 'default')
    {
        if (!isset(static::$instances[$id])) {
            static::$instances[$id] = new self();
        }
        
        return static::$instances[$id];
    }
    
    /**
     * Set document root path.
     *
     * @param string $root Path to document root.
     *
     * @return string
     */
    public static function setRoot($root)
    {
        if (!$root = realpath($root)) {
            throw new InvalidArgumentException("Path root - {$root} doesn't exists.");
        }
        static::$root = $root;
        
        return $root;
    }
    
    /**
     * Return path to ROOT FOLDER FOR ACTUAL INSTANCE.
     * @return string
     * @throws \ErrorException
     */
    public function root()
    {
        if ($this->aliases['@root'] === null) {
            $this->aliases['@root'] = static::$root;
        }
        
        return $this->path('@root');
    }
    
    /**
     * Translate path alias into a actual path or build path.
     * Return absolute path.
     *
     * @param string $path Path to the file
     *
     * @return mixed|string
     */
    public function path($path)
    {
        $path  = strtolower(trim($path));
        $first = substr($path, 0, 1);
        
        if ($first == '@' && isset($this->aliases[$path])) {
            $path = $this->aliases[$path];
            
        } elseif ($first == '/') {
            $path = preg_replace('/\\//', $this->root() . '/', $path, 1);
            
        }
        if ($path = realpath($path)) {
            $path = static::clean($path);
        }
        
        return $path;
    }
    
    /**
     *
     * @param string $alias Alias name. The key has to begin with a symbol '@'.
     * @param string $path  The path must to be absolute.
     *
     * @return string|boolean
     */
    public function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }

        if ($path = $this->path($path)) {
            $alias = strtolower(trim($alias));
            
            $this->aliases[$alias] = $path;
        }
        
        return $path;
    }
    
    /**
     * Clean path and replace backslashes.
     *
     * @param string $path
     *
     * @return mixed
     */
    public static function clean($path)
    {
        return str_replace('\\', '/', $path);
    }
    
    /**
     * Check if file exists and it's a file.
     *
     * @param string $path Path to the file.
     *
     * @return bool
     */
    public static function isFile($path)
    {
        return is_file($path);
    }
    
    /**
     * Close __construct.
     * @throws PathException Throws PathException if path root cannot be set.
     */
    protected function __construct()
    {
        if (empty(static::$root)) {
            $root = $_SERVER['DOCUMENT_ROOT'];

            if (empty($root)) {
                throw new PathException(
                    "Path root not set."
                );
            }

            static::$root = $root;
        }
    }
    
    /**
     * Close __clone.
     */
    protected function __clone(){}
    
    /**
     * Close __wakeUp.
     */
    protected function __wakeUp(){}
}
