<?php
/**
 * sfHyperian PHP Backend
 *
 * by Matthias Weiß <info@codeandcreate.de>
 * https://github.com/codeandcreate/sfHyperion
 */

include('conf/Configuration.php');
$returnValues = ['code' => false, 'message' => ''];

if ($_POST['apiKey'] === $config['apiKey']) {

  if ($config['dummy'] === true) {
    include('lib/RemoteCommand_dummy.class.php');
  } else {
    include('lib/RemoteCommand.class.php');
  }

  session_start();

  if (empty($_SESSION))
  {
      $_SESSION['priority'] = array();
      $_SESSION['colour'] = '00FFFF';
      $_SESSION['duration'] = -1;
  }

  $com = new RemoteCommand();

  if (isset($_POST['command'])) {
    switch($_POST['command']) {
      case 'on':
        $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
            ->withController($config['serverController'])
            ->withSleep(2)
            ->callOn();
        if ($return) {
          $returnValues['code'] = true;
        }
        break;
      case 'off':
        $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
            ->withController($config['serverController'])
            ->withSleep(1)
            ->callOff();
        if ($return) {
          $returnValues['code'] = true;
        }
        break;
      case 'clear':
        if (isset($_POST['priority'])) {
          $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
              ->withAddress($config['hyperionAddress'])
              ->withPriority($_POST['priority'])
              ->callClear();
          unset($_SESSION['priority'][$_POST['priority']]);
          if ($return) {
            $returnValues['code'] = true;
          }
        } else {
          $returnValues['message'] = "PRIORITY_NOT_SET";
        }
        break;
      case 'clearAll':
        $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
            ->withAddress($config['hyperionAddress'])
            ->callClearAll();
        $_SESSION['priority'] = array();
        if ($return) {
          $returnValues['code'] = true;
        }
        break;
      case 'changeColor':
        if (isset($_POST['duration']) && $_POST['priority'] && $_POST['colour']) {
          $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
              ->withAddress($config['hyperionAddress'])
              ->withDuration($_POST['duration'] > 0 ? $_POST['duration'] : false)
              ->withPriority($_POST['priority'])
              ->withColour($_POST['colour'])
              ->callColour();
          $_SESSION['colour'] = $_POST['colour'];
          $_SESSION['priority'][$_POST['priority']] = true;
          $_SESSION['duration'] = $_POST['duration'];
          if ($return) {
              $returnValues['code'] = true;
          }
        } else {
          $returnValues['message'] = "COLOR_SETTINGS_NOT_SET";
        }
        break;
      case 'effect':
        if (isset($_POST['duration']) && $_POST['priority'] && $_POST['effect']) {
          $return = $com->withServer($config['serverAddress'], $config['serverUsername'], $config['serverPassword'], $config['debug'])
              ->withAddress($config['hyperionAddress'])
              ->withDuration($_POST['duration'] > 0 ? $_POST['duration'] : false)
              ->withPriority($_POST['priority'])
              ->withEffect($_POST['effect'])
              ->callEffect();
          $_SESSION['priority'][$_POST['priority']] = true;
          $_SESSION['duration'] = $_POST['duration'];
          if ($return) {
              $returnValues['code'] = true;
          }
        } else {
          $returnValues['message'] = "EFFECT_SETTINGS_NOT_SET";
        }
        break;
      default:
        $return = $com->callDefault();
        break;
    }
  } else {
    $returnValues['message'] = "NO_COMMAND_SET";
  }
} else {
  $returnValues['message'] = 'INVALID_API_KEY';
}

header('Content-Type: application/json');
echo json_encode($returnValues);
?>
