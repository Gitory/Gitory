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
imports:
    - import.yml
debug: false
type: email
YAML
        )->url();

        $this->fileLocator->locate('import.yml', dirname($importedResource), false)->willReturn($importedResource);
        $this->load($resource)->shouldLoadConfig(['debug' => false, 'database' => 'mysql', 'type' => 'email'], 2);
    }

    public function it_loads_merge_imported_files()
    {

        $importedResource = $this->fileSystem->createFile('/import.yml', <<<YAML
database:
    host: localhost
    port: 4567
YAML
        )->url();

        $resource = $this->fileSystem->createFile('/config.yml', <<<YAML
imports:
    - import.yml
database:
    host: db-master
YAML
        )->url();

        $this->fileLocator->locate('import.yml', dirname($importedResource), false)->willReturn($importedResource);
        $this->load($resource)->shouldLoadConfig(['database' => ['host' => 'db-master', 'port' => 4567]], 2);
    }

    public function it_loads_optional_imported_yaml_files_if_absent()
    {
        $importedResource = $this->fileSystem->createFile('/import.yml', <<<YAML
debug: true
YAML
        )->url();

        $resource = $this->fileSystem->createFile('/config.yml', <<<YAML
debug: false
imports_after_if_exists:
    - import.yml
YAML
        )->url();

        $this->fileLocator->locate('import.yml', dirname($importedResource), false)->willReturn($importedResource);
        $this->load($resource)->shouldLoadConfig(['debug' => true], 2);
    }

    public function it_ignores_optional_imported_yaml_files_if_absent()
    {
        $resource = $this->fileSystem->createFile('/config.yml', <<<YAML
debug: false
imports_after_if_exists:
    - import.yml
YAML
        )->url();

        $this->load($resource)->shouldLoadConfig(['debug' => false], 1);
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
