<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;
use GitElephant\Repository as GitRepository;

/**
 * Behat context class.
 */
class CliContext implements SnippetAcceptingContext
{
    /**
     * Cli command builder
     * @var Symfony\Component\Process\ProcessBuilder
     */
    private $processBuilder;

    /**
     * Command process
     * @var Symfony\Component\Process\Process
     */
    private $process;

    /**
     * @When I execute :command
     */
    public function iExecute($command)
    {
        $builder = $this->getProcessBuilder();
        $builder->setArguments(explode(' ', $command));
        $this->process = $builder->getProcess();
        $this->process->run();
    }

    /**
     * @Then the output should contains
     */
    public function theOutputShouldContains(PyStringNode $output)
    {
        $processOutput = $this->process->getOutput();
        $behatOutputTemplate = (string)$output;

        if(strpos($processOutput, $behatOutputTemplate) === false) {
            $diff = new \Diff(
                explode(PHP_EOL, print_r($behatOutputTemplate.PHP_EOL, true)),
                explode(PHP_EOL, print_r($processOutput, true))
            );
            $renderer = new \Diff_Renderer_Text_Unified;
            $stringDiff = $diff->render($renderer);

            throw new Exception('Output does not match: '. PHP_EOL . $stringDiff);
        }
    }

    /**
     * @Then the exception output should be
     */
    public function theExceptionOutputShouldBe(PyStringNode $output)
    {
        $processErrorOutput = $this->process->getErrorOutput();
        preg_match('/\[Exception\]\s*([^\n]*)/m', $processErrorOutput, $matches);

        if(count($matches) !== 2) {
            throw new Exception('Exception not found');
        }

        $processExceptionOutput = trim($matches[1]);
        $behatOutputTemplate = (string)$output;

        $diff = new \Diff(
            explode(PHP_EOL, print_r($behatOutputTemplate, true)),
            explode(PHP_EOL, print_r($processExceptionOutput, true))
        );

        $renderer = new \Diff_Renderer_Text_Unified;
        $stringDiff = $diff->render($renderer);
        if($stringDiff !== "") {
            throw new Exception('Exception output does not match: '. PHP_EOL . $stringDiff);
        }
    }

    /**
     * @Then the git repository :identifier exists
     */
    public function theGitRepositoryExists($identifier)
    {
        $gitRepository = GitRepository::open(__DIR__.'/../../private/test/repositories/'.$identifier);
    }

    /**
     * @Then the :identifier repository does not exists
     */
    public function theRepositoryDoesNotExists($identifier)
    {
        if(is_dir(__DIR__.'/../../private/test/repositories/'.$identifier)) {
            throw new Exception('Repository '.$identifier.' exists');
        }
    }

    /**
     * @Given the :identifier repository folder exists
     */
    public function theRepositoryFolderExists($identifier)
    {
        mkdir(__DIR__.'/../../private/test/repositories/'.$identifier, 0777, true);
    }

    /**
     * @Given the :identifier repository folder does not exists
     */
    public function theRepositoryFolderDoesNotExists($identifier)
    {
        $this->removeDirectory(__DIR__.'/../../private/test/repositories/'.$identifier);
    }

    /**
     * Get Cli command builder
     * @return Symfony\Component\Process\ProcessBuilder
     */
    private function getProcessBuilder()
    {
        if($this->processBuilder === null) {
            $finder = new PhpExecutableFinder;
            $php = $finder->find();

            $this->processBuilder = new ProcessBuilder();
            $this->processBuilder->setPrefix(array($php, __DIR__.'/../../bin/gitory', '--env=test'));
        }

        return $this->processBuilder;
    }

    /**
     * Remove a directory recursively
     * @param  string $path directory path
     */
    private function removeDirectory($path)
    {
        if(!is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir((string)$file);
            }
        }
    }
}
