<?php

namespace Gitory\Gitory\Config;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

class YamlLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $this->setCurrentDir(dirname($resource));
        $resources = [new FileResource($resource)];
        $parsed = Yaml::parse($resource);
        if (isset($parsed['imports'])) {
            list($parsed, $resources) = $this->imports($parsed, $resources, 'imports', false, false);
        }
        if (isset($parsed['imports_after_if_exists'])) {
            list($parsed, $resources) = $this->imports($parsed, $resources, 'imports_after_if_exists', true, true);
        }
        return [$parsed, $resources];
    }

    /**
     * parses other ressources and merge with config
     * @param  Array   $parsed    already parsed config
     * @param  Array   $resources already parsed ressources
     * @param  string  $key       key containing ressources path to be parsed and merged
     * @param  boolean $optional  true to ignore non existing ressources
     * @param  boolean $overwrite true to overwrite config with ressources config, false to default to ressources config
     */
    private function imports(Array $parsed, Array $resources, $key, $optional, $overwrite)
    {
        foreach ($parsed[$key] as $file) {
            list($parsed, $resources) = $this->importsFile($parsed, $resources, $file, $optional, $overwrite);
        }
        unset($parsed[$key]);
        return [$parsed, $resources];
    }

    /**
     * parses other ressources and merge with config
     * @param  Array   $parsed    already parsed config
     * @param  Array   $resources already parsed ressources
     * @param  string  $file      ressource file to be parsed and merged
     * @param  boolean $optional  true to ignore non existing ressource
     * @param  boolean $overwrite true to overwrite config with ressource config, false to default to ressource config
     */
    private function importsFile(Array $parsed, Array $resources, $file, $optional, $overwrite)
    {
        list($subConfig, $subResources) = $this->import($file);
        if ($subConfig === null && $optional) {
            return [$parsed, $resources];
        }
        if ($overwrite) {
            $parsed = array_replace_recursive($parsed, $subConfig);
        } else {
            $parsed = array_replace_recursive($subConfig, $parsed);
        }
        $resources = array_merge_recursive($subResources, $resources);
        return [$parsed, $resources];
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
