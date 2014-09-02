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
            foreach ($parsed['imports'] as $file) {
                list($subConfig, $subResources) = $this->import($file);
                $parsed += $subConfig;
                $resources = array_merge($subResources, $resources);
            }

            unset($parsed['imports']);
        }

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
