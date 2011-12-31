<?php

namespace Behat\CommonContexts;

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext;

/**
 * Before requireing (or adding to autoload) this context,
 * don't forget to set this constant:
 *
 *  define('BEHAT1_FEATURES_PATH', '');
 *
 * which must point to Behat1 `features` path.
 */
if (!defined('BEHAT1_FEATURES_PATH')) {
    throw new \RuntimeException(
        'Set BEHAT1_FEATURES_PATH constant before including Behat1BCContext.'
    );
} elseif (!file_exists(BEHAT1_FEATURES_PATH)) {
    throw new \RuntimeException(
        'Provided BEHAT1_FEATURES_PATH: "'.BEHAT1_FEATURES_PATH.'" does not exists.'
    );
}

if (file_exists(BEHAT1_FEATURES_PATH.'/support/bootstrap.php')) {
    require_once BEHAT1_FEATURES_PATH.'/support/bootstrap.php';
}

/**
 * Provides backward compatibility with Behat 1.x.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Behat1BCContext extends BehatContext implements ClosuredContextInterface, TranslatedContextInterface
{
    public $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        if (file_exists(BEHAT1_FEATURES_PATH.'/support/env.php')) {
            $world = $this;
            require(BEHAT1_FEATURES_PATH.'/support/env.php');
        }
    }

    public function getStepDefinitionResources() {
        if (file_exists(BEHAT1_FEATURES_PATH.'/steps')) {
            return glob(BEHAT1_FEATURES_PATH.'/steps/*.php');
        }
        return array();
    }

    public function getHookDefinitionResources() {
        if (file_exists(BEHAT1_FEATURES_PATH.'/support/hooks.php')) {
            return array(BEHAT1_FEATURES_PATH.'/support/hooks.php');
        }
        return array();
    }

    public function getTranslationResources() {
        if (file_exists(BEHAT1_FEATURES_PATH.'/steps/i18n')) {
            return glob(BEHAT1_FEATURES_PATH.'/steps/i18n/*.xliff');
        }
        return array();
    }

    public function __call($name, array $args) {
        if (isset($this->$name) && is_callable($this->$name)) {
            return call_user_func_array($this->$name, $args);
        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Call to undefined method ' . get_class($this) . '::' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_ERROR
            );
        }
    }
}
