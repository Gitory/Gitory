<?php

namespace spec\Gitory\Gitory\Config;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use VirtualFileSystem\FileSystem;
use Exception;

class YamlLoaderSpec extends ObjectBehavior
{
    private $fileSystem;

    private $fileLocator;

    public function let(FileLocatorInterface $fileLocator)
    {
        $this->fileSystem = new FileSystem();
        $this->fileLocator = $fileLocator;
        $this->beConstructedWith($this->fileLocator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gitory\Gitory\Config\YamlLoader');
    }

    public function it_supports_yaml_files()
    {
        $resource = $this->fileSystem->path('/config.yml');
        $this->supports($resource)->shouldReturn(true);
    }

    public function it_does_not_supports_other_files()
    {
        $resource = $this->fileSystem->path('/config.ini');
        $this->supports($resource)->shouldReturn(false);
    }

    public function it_loads_a_yaml_file()
    {
        $resource = $this->fileSystem->createFile('/config.yml', 'debug: true')->url();
        $this->load($resource)->shouldLoadConfig(['debug' => true], 1);
    }

    public function it_loads_imported_yaml_files()
    {

        $importedResource = $this->fileSystem->createFile('/import.yml', <<<YAML
debug: true
database: mysql
YAML
        )->url();

        $resource = $this->fileSystem->createFile('/config.yml', <<<YAML
debug: false
type: email
imports:
    - import.yml
YAML
        )->url();

        $this->fileLocator->locate('import.yml', dirname($importedResource), false)->willReturn($importedResource);
        $this->load($resource)->shouldLoadConfig(['debug' => false, 'type' => 'email', 'database' => 'mysql'], 2);
    }

    public function getMatchers()
    {
        return [
            'loadConfig' => function ($subject, $config, $resourceCount) {
                list($subjectConfig, $subjectResources) = $subject;

                if(count($subjectResources) !== $resourceCount) {
                    return false;
                }

                if($subjectConfig !== $config) {
                    return false;
                }

                return true;
            }
        ];
    }
}
