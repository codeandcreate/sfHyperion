<?php

/**
 * Remote Command Class - Dummy
 *
 * @library Lib
 * @author  Matthias Weiß <info@codeandcreate.de>
 */

class RemoteCommand
{
  private $logFileName = "";
  /**
   * Command
   *
   * @var string
   */
  const COMMAND = '/usr/bin/hyperion-remote %s';

  /**
   * Command application
   *
   * @var string
   */
  const APPLICATION = 'hyperion';

  /**
   * Command on argument
   *
   * @var string
   */
  const ARGUMENT_ON = '%s start %s';

  /**
   * Command off argument
   *
   * @var string
   */
  const ARGUMENT_OFF = '%s stop %s';

  /**
   * Command address argument
   *
   * @var string
   */
  const ARGUMENT_ADDRESS = ' --address %s ';

  /**
   * Command priority argument
   *
   * @var string
   */
  CONST ARGUMENT_PRIORITY = ' --priority %s ';

  /**
   * Command duration argument
   *
   * @var string
   */
  CONST ARGUMENT_DURATION = ' --duration %s ';

  /**
   * Command colour argument
   *
   * @var string
   */
  CONST ARGUMENT_COLOUR = ' --color %s ';

  /**
   * Command effect argument
   *
   * @var string
   */
  CONST ARGUMENT_EFFECT = ' --effect \'%s\' ';

  /**
   * Command server
   *
   * @var string
   */
  protected $server = false;

  /**
   * Command server username
   *
   * @var string
   */
  protected $username = false;

  /**
   * Command server password
   *
   * @var string
   */
  protected $password = false;

  /**
   * Command address
   *
   * @var string
   */
  protected $address = false;

  /**
   * Command controller
   *
   * @var string
   */
  protected $controller = false;

  /**
   * Command priority as an integer
   *
   * @var integer
   */
  protected $priority = false;

  /**
   * Command duration set in milliseconds
   *
   * @var integer
   */
  protected $duration = false;

  /**
   * Command colour property value in HEX
   *
   * @var string
   */
  protected $colour = false;

  /**
   * Command effect property value as a string
   *
   * @var string
   */
  protected $effect = false;

  /**
   * Command post sleep period property value in seconds
   *
   * @var string
   */
  protected $sleep = false;

  /**
   * Command output
   *
   * @var string
   */
  protected $output;

  /**
   * Command debug
   *
   * @var boolean
   */
  protected $debug = false;


  public function __construct()
  {
    $this->logFileName = dirname(__FILE__) . "/../log/" . date("Y-m-d") . "_dummy.log";

    $this->_logLine("Dummy Start.");
  }

  private function _logLine ($message)
  {
    return file_put_contents($this->logFileName, date("Y-m-d H:i:s") . " | " . $message . "\n\n", FILE_APPEND);
  }

  protected function controllerType($argument)
  {
    switch ($this->controller) {
        case '/etc/init.d':
        case 'sudo /etc/init.d':
            return sprintf($argument, $this->controller . '/' . self::APPLICATION, '');
            break;
        case '/sbin/initctl':
        default:
            return sprintf($argument, $this->controller, self::APPLICATION);

    }
  }

  public function callOn()
  {
      $this->_logLine("CALL ON");

      $result = $this->executeCommand($this->controllerType(self::ARGUMENT_ON), true);

      return $result;
  }

  public function callOff()
  {
      $result = $this->executeCommand($this->controllerType(self::ARGUMENT_OFF), true);

      return $result;
  }

    public function callClear()
    {
        return $this->executeCommand('--clear');
    }

    public function callClearAll()
    {
        return $this->executeCommand('--clearall');
    }

    public function callColour()
    {
        if ($this->colour) {
            return $this->executeCommand(sprintf(self::ARGUMENT_COLOUR, $this->colour));
        }

        return false;
    }

    public function callEffect()
    {
        if ($this->effect) {
            return $this->executeCommand(sprintf(self::ARGUMENT_EFFECT, $this->effect));
        }

        return false;
    }

    public function callDefault()
    {
        return false;
    }

    public function getEffects()
    {
        $this->executeCommand('--list | grep \'"name" : \' | cut -d \'"\' -f4 | tr \'\\n\' \',\'');

        return $this->extractData();
    }

    public function getStatus()
    {
        $this->executeCommand('ps aux | grep [h]yperion | awk \'{print $11}\'', true);

        if ($this->output === null) {
            return false;
        }

        return true;
    }

    public function getCommands()
    {
        $this->executeCommand('--list | grep \'"priority" : \' | cut -d \':\' -f2 | tr \'\\n\' \',\'');

        return $this->extractData();
    }

    protected function extractData()
    {
        $array = explode(',', $this->output);

        if (count($array) > 0) {
            array_splice($array, -1);
        }

        return $array;
    }

    protected function resetArguments()
    {
        $this->server = false;
        $this->username = false;
        $this->password = false;
        $this->address = false;
        $this->controller = false;
        $this->priority = false;
        $this->duration = false;
        $this->colour = false;
        $this->effect = false;
        $this->sleep = false;
        $this->debug = false;
    }

    public function withServer($address, $username, $password, $debug)
    {
        $this->server = (string) $address;
        $this->username = (string) $username;
        $this->password = (string) $password;
        $this->debug = (boolean) $debug;

        $this->_logLine("SET SERVER VARS: ".json_encode([$address, $username, $password, $debug]));

        return $this;
    }

    public function withAddress($value)
    {
        $this->address = (string) $value;

        return $this;
    }

    public function withController($controller)
    {
        $this->controller = (string) $controller;

                $this->_logLine("SET CONTROLLER: ".$controller);

        return $this;
    }

    public function withPriority($value)
    {
        $this->priority = (int) $value;

        return $this;
    }

    public function withDuration($value)
    {
        $this->duration = (int) ($value * 1000);

        return $this;
    }

    public function withColour($value)
    {
        $this->colour = (string) $value;

        return $this;
    }

    public function withEffect($value)
    {
        $this->effect = (string) $value;

        return $this;
    }

    public function withSleep($value)
    {
        $this->sleep = (int) $value;

        return $this;
    }

    public function withDebug($value)
    {
        $this->debug = $value;

        return $this;
    }

    private function executeCommand($command, $overwriteCommand = false)
    {
        if (!$this->server) {
            return false;
        }

        if (!$overwriteCommand) {
            if ($this->address) {
                $command = sprintf(self::ARGUMENT_ADDRESS, $this->address) . $command;
            }

            if ($this->priority) {
                $command .= sprintf(self::ARGUMENT_PRIORITY, $this->priority);
            }

            if ($this->duration) {
                $command .= sprintf(self::ARGUMENT_DURATION, $this->duration);
            }

            $command = sprintf(self::COMMAND, $command);
        }

        if ($this->debug) {
            error_log("Executing '" . self::APPLICATION . "' command to '{$this->server}': {$command}");
        }

        if ($this->server == '127.0.0.1' || $this->server == getHostByName(getHostName())) {
            $this->output = "dummy";
            $this->_logLine( __FUNCTION__ . " => shell_exec(" . $command . ")");
        } else {
            $connection = ssh2_connect($this->server, 22);

            if (!$this->username) {
                return false;
            }

            $this->_logLine( __FUNCTION__ . " => ssh2_exec(" . $command . ")");
        }

        if ($this->sleep) {
            sleep($this->sleep);
        }

        $this->resetArguments();

        if ($this->output === null) {
            return false;
        }

        return true;
    }
}
