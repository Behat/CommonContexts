<?php

namespace Behat\CommonContexts;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Context\BehatContext;

/**
 * Provides some steps/methods which are useful for testing a Symfony2 application.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SymfonyExtraContext extends BehatContext
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Then /^no email should have been sent$/
     */
    public function noEmailShouldHaveBeenSent()
    {
        if (0 < $count = $this->loadProfile()->getCollector('swiftmailer')->getMessageCount()) {
            throw new \RuntimeException(sprintf('Expected no email to be sent, but %d emails were sent.', $count));
        }
    }

    /**
     * @Then /^email with subject "([^"]*)" should have been sent(?: to "([^"]+)")?$/
     */
    public function emailWithSubjectShouldHaveBeenSent($subject, $to = null)
    {
        $mailer = $this->loadProfile()->getCollector('swiftmailer');
        if (0 === $mailer->getMessageCount()) {
            throw new \RuntimeException('No emails have been sent.');
        }

        $foundToAddresses = null;
        $foundSubjects = array();
        foreach ($mailer->getMessages() as $message) {
            $foundSubjects[] = $message->getSubject();

            if ($subject === $message->getSubject()) {
                $foundToAddresses = implode(', ', array_keys($message->getTo()));

                if (null !== $to) {
                    $toAddresses = $message->getTo();
                    if (array_key_exists($to, $toAddresses)) {
                        // found, and to address matches
                        return;
                    }

                    // check next message
                    continue;
                } else {
                    // found, and to email isn't checked
                    return;
                }

                // found
                return;
            }
        }

        if (!$foundToAddresses) {
            if (!empty($foundSubjects)) {
                throw new \RuntimeException(sprintf('Subject "%s" was not found, but only these subjects: "%s"', $subject, implode('", "', $foundSubjects)));
            }

            // not found
            throw new \RuntimeException(sprintf('No message with subject "%s" found.', $subject));
        }

        throw new \RuntimeException(sprintf('Subject found, but "%s" is not among to-addresses: %s', $to, $foundToAddresses));
    }

    /**
     * Loads the profiler's profile.
     *
     * If no token has been given, the debug token of the last request will
     * be used.
     *
     * @param string $token
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     * @throws \RuntimeException
     */
    public function loadProfile($token = null)
    {
        if (null === $token) {
            $headers = $this->getMinkContext()->getSession()->getResponseHeaders();

            if (!isset($headers['X-Debug-Token']) && !isset($headers['x-debug-token'])) {
                throw new \RuntimeException('Debug-Token not found in response headers. Have you turned on the debug flag?');
            }
            $token = isset($headers['X-Debug-Token']) ? $headers['X-Debug-Token'] : $headers['x-debug-token'];
        }

        return $this->kernel->getContainer()->get('profiler')->loadProfile($token);
    }

    /**
     * Gets the Mink context.
     *
     * If you are using MinkContext as a subcontext instead of using it as
     * the main one, overwrite this method
     *
     * @return \Behat\Mink\Behat\Context\BaseMinkContext
     */
    protected function getMinkContext()
    {
        return $this->getMainContext();
    }
}
