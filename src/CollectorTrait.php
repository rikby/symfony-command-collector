<?php
namespace Rikby\SymfonyConsole\CommandCollector;

/**
 * This class can find commands by paths and merge them into a file
 * This script will try to find files [COMMAND_NAME]-app-include.php in provided paths
 *
 * File [COMMAND_NAME]-app-include.php should contain code which will add a command into console application.
 *   $app->add(new CoolCommand());
 *
 * @package Rikby\SymfonyConsole\CommandCollector
 */
trait CollectorTrait
{
    /**
     * Input command name
     *
     * @var string
     */
    protected $inputCommandName;

    /**
     * Paths lists to look up command include file
     *
     * @var array
     */
    protected $commandPaths;

    /**
     * Path to compiled file
     *
     * @var string
     */
    protected $compiledFile;

    /**
     * Commands file suffix
     *
     * @var string
     */
    protected $includeFileFormat = '%s-app-include.php';

    /**
     * Capture commands code
     *
     * @return string
     */
    public function captureCommandsCode()
    {
        $this->validateName();

        return $this->mergeCode($this->searchCommandFiles());
    }

    /**
     * Capture commands and compile code into the file
     *
     * @return string   Path to compiled file
     */
    public function captureCommands()
    {
        if (!$this->isCompiledFileExist()) {
            $this->compileAndWrite();
        }

        return $this->compiledFile;
    }

    /**
     * Set compiled file
     *
     * @param string $file
     * @return $this
     */
    public function setCompiledFile($file)
    {
        $this->compiledFile = $file;

        return $this;
    }

    /**
     * Set compiled file
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->inputCommandName = $name;

        return $this;
    }

    /**
     * Set paths to command file
     *
     * Path can be /path/{@*}to/{@*}/bin
     *
     * @param array $paths
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->commandPaths = $paths;

        return $this;
    }

    /**
     * Capture commands and compile code into the file
     *
     * File will overwritten
     *
     * @return string   Path to compiled file
     */
    protected function forceCaptureCommands()
    {
        $this->compileAndWrite();

        return $this->compiledFile;
    }

    /**
     * Merge code from files
     *
     * @param array $commandFiles
     * @return string
     */
    protected function mergeCode($commandFiles)
    {
        $code    = '<?php'.PHP_EOL;
        $printer = new \PHPParser_PrettyPrinter_Default();
        $parser  = new \PHPParser_Parser(new \PHPParser_Lexer);
        foreach ($commandFiles as $file) {
            $code .= $printer->prettyPrint(
                    $parser->parse(file_get_contents($file))
                ).PHP_EOL;
        }

        return $code;
    }

    /**
     * Search command files
     *
     * @return array
     */
    protected function searchCommandFiles()
    {
        $files = [];
        foreach ($this->commandPaths as $path) {
            $files = array_merge(
                $files,
                glob(realpath($path).'/'.sprintf($this->includeFileFormat, $this->inputCommandName))
            );
        }

        return $files;
    }

    /**
     * Validate command name
     *
     * @throws Exception
     * @return $this
     */
    protected function validateName()
    {
        if (empty($this->inputCommandName)) {
            throw new Exception('Command name must be set with end-point command name value.');
        }

        if (!preg_match('/([A-z_-.]+)/', $this->inputCommandName)) {
            throw new Exception('Invalid command name. Please follow this format: ([A-z_-.]+)');
        }

        return $this;
    }

    /**
     * Check if the file with merged code is exist
     *
     * @return bool
     */
    protected function isCompiledFileExist()
    {
        return !is_file($this->compiledFile);
    }

    /**
     * Merge code and write into the file
     *
     * @throws Exception
     * @return $this
     */
    protected function compileAndWrite()
    {
        if (!file_put_contents($this->compiledFile, $this->captureCommandsCode())) {
            throw new Exception('Cannot write content into file '.$this->compiledFile);
        }

        return $this;
    }
}
