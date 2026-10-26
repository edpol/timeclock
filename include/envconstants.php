<?php
/*
 * read .env file and define constants
 */
class EnvConstants
{
    /**
     * The directory where the .env file can be located.
     *
     * @var string
     */
    protected string $env_file;


    public function __construct(string $env_file)
    {
        if(!file_exists($env_file)) {
            echo "<pre>";
            throw new InvalidArgumentException(sprintf('%s does not exist', $env_file));
        }
        $this->env_file = $env_file;
    }

    public function load() :void
    {
        if (!is_readable($this->env_file)) {
            throw new RuntimeException(sprintf('%s file is not readable', $this->env_file));
        }

        $lines = file($this->env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);

            // remove new line, return, tab, vertical tab, integer 0 and double quote
            $name  = trim($name, " \n\r\t\v\x00'\"");
            $value = trim($value," \n\r\t\v\x00'\"");

            if(strtolower($value)=='true' ) $value = true;
            if(strtolower($value)=='false') $value = false;

            if(!defined($name)) define($name, $value);
            if (!array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}
