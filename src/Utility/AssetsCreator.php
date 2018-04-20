<?php
/**
 * This file is part of Assets.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/assets
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @see         https://github.com/matthiasmullie/minify
 */
namespace Assets\Utility;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\File;
use InvalidArgumentException;
use MatthiasMullie\Minify;
use RuntimeException;

/**
 * An utility to create assets
 */
class AssetsCreator
{
    /**
     * Asset full path
     * @var string
     */
    protected $asset = null;

    /**
     * File paths that will be transformed into a single asset
     * @var array
     */
    protected $paths = [];

    /**
     * Asset type (`css` or `js`)
     * @var string
     */
    protected $type = null;

    /**
     * Construct. Sets the asset type and paths
     * @param string|array $paths String or array of css files
     * @param string $type Extension (`css` or `js`)
     * @return $this
     * @throws InvalidArgumentException
     * @uses resolveAssetPath()
     * @uses resolveFilePaths()
     * @uses $asset
     * @uses $paths
     * @uses $type
     */
    public function __construct($paths, $type)
    {
        if (!in_array($type, ['css', 'js'])) {
            throw new InvalidArgumentException(__d('assets', 'Asset type `{0}` not supported', $type));
        }

        //Note: `resolveFilePaths()` method needs `$type` property;
        //  `resolveAssetPath()` method needs `$type` and `$paths` properties
        $this->type = $type;
        $this->paths = $this->resolveFilePaths($paths);
        $this->asset = $this->resolveAssetPath();

        return $this;
    }

    /**
     * Internal method to resolve the asset path
     * @return string Asset full path
     * @use $paths
     * @use $type
     */
    protected function resolveAssetPath()
    {
        $basename = md5(serialize(array_map(function ($path) {
            return [$path, filemtime($path)];
        }, $this->paths)));

        return Configure::read(ASSETS . '.target') . DS . $basename . '.' . $this->type;
    }

    /**
     * Internal method to resolve partial file paths and return full paths
     * @param string|array $paths Partial file paths
     * @return array Full file paths
     * @throws RuntimeException
     * @use $type
     */
    protected function resolveFilePaths($paths)
    {
        $loadedPlugins = Plugin::loaded();

        return array_map(function ($path) use ($loadedPlugins) {
            $pluginSplit = pluginSplit($path);

            //Note that using `pluginSplit()` is not sufficient, because
            //  `$path` may still contain a dot
            if (!empty($pluginSplit[0]) && in_array($pluginSplit[0], $loadedPlugins)) {
                list($plugin, $path) = $pluginSplit;
            }

            $path = substr($path, 0, 1) === '/' ? substr($path, 1) : $this->type . DS . $path;
            $path = DS === '/' ? $path : $path = str_replace('/', DS, $path);
            $path = empty($plugin) ? WWW_ROOT . $path : Plugin::path($plugin) . 'webroot' . DS . $path;

            //Appends the file extension, if not already present
            $path = pathinfo($path, PATHINFO_EXTENSION) == $this->type ? $path : sprintf('%s.%s', $path, $this->type);

            if (!is_readable($path)) {
                throw new RuntimeException(__d('assets', 'File `{0}` doesn\'t exist', rtr($path)));
            }

            return $path;
        }, (array)$paths);
    }

    /**
     * Creates the asset
     * @return string
     * @throws RuntimeException
     * @uses filename()
     * @uses path()
     * @uses $paths
     * @uses $type
     */
    public function create()
    {
        if (!is_readable($this->path())) {
            switch ($this->type) {
                case 'css':
                    $minifier = new Minify\CSS();
                    break;
                case 'js':
                    $minifier = new Minify\JS();
                    break;
            }

            array_map([$minifier, 'add'], $this->paths);

            //Writes the file
            if (!(new File($this->path(), false, 0755))->write($minifier->minify())) {
                throw new RuntimeException(__d('assets', 'Failed to create file {0}', rtr($this->path())));
            }
        }

        return $this->filename();
    }

    /**
     * Returns the asset filename
     * @return string Asset filename
     * @uses path()
     */
    public function filename()
    {
        return pathinfo($this->path(), PATHINFO_FILENAME);
    }

    /**
     * Returns the asset full path
     * @return string Asset full path
     */
    public function path()
    {
        return $this->asset;
    }
}
