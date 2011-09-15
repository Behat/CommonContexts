<?php

namespace Behat\CommonContext;

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
        foreach ($mailer->getMessages() as $message) {
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
     * @throws \RuntimeException
     */
    public function loadProfile($token = null)
    {
        if (null === $token) {
            $headers = $this->getMainContext()->getSession()->getResponseHeaders();

            if (!isset($headers['X-Debug-Token'])) {
                throw new \RuntimeException('Debug-Token not found in response headers. Have you turned on the debug flag?');
            }
            $token = $headers['X-Debug-Token'];
        }

        return $this->kernel->getContainer()->get('profiler')->loadProfile($token);
    }
}
