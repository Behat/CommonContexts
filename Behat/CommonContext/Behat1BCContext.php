<?php

namespace Behat\CommonContext;

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext;

/**
 * In your context - add:
 *
 *  define('BEHAT1_SUPPORT_PATH', __DIR__.'/../support');
 *
 * in order to be able to load env.php and bootstrap.php configs
 */
if (defined('BEHAT1_SUPPORT_PATH')) {
    if (file_exists(BEHAT1_SUPPORT_PATH.'/bootstrap.php')) {
        require_once BEHAT1_SUPPORT_PATH.'/bootstrap.php';
    }
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

        if (defined('BEHAT1_SUPPORT_PATH')) {
            if (file_exists(BEHAT1_SUPPORT_PATH.'/env.php')) {
                $world = $this;
                require(BEHAT1_SUPPORT_PATH.'/env.php');
            }
        }
    }

    public function getStepDefinitionResources() {
        if (file_exists(__DIR__ . '/../steps')) {
            return glob(__DIR__ . '/../steps/*.php');
        }
        return array();
    }

    public function getHookDefinitionResources() {
        if (file_exists(__DIR__ . '/../support/hooks.php')) {
            return array(__DIR__ . '/../support/hooks.php');
        }
        return array();
    }

    public function getTranslationResources() {
        if (file_exists(__DIR__ . '/../steps/i18n')) {
            return glob(__DIR__ . '/../steps/i18n/*.xliff');
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
