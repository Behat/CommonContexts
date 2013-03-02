<?php

namespace Behat\CommonContexts;

use Behat\Behat\Context\BehatContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides some steps/methods which are useful for testing a Symfony2 application.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SymfonyMailerContext extends RawMinkContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     *
     * @return null
     */
    public function setKernel(KernelInterface $kernel)
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

            if (trim($subject) === trim($message->getSubject())) {
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
            $headers = $this->getSession()->getResponseHeaders();

            if (!isset($headers['X-Debug-Token']) && !isset($headers['x-debug-token'])) {
                throw new \RuntimeException('Debug-Token not found in response headers. Have you turned on the debug flag?');
            }
            $token = isset($headers['X-Debug-Token']) ? $headers['X-Debug-Token'] : $headers['x-debug-token'];
            if (is_array($token)) {
                $token = end($token);
            }
        }

        return $this->kernel->getContainer()->get('profiler')->loadProfile($token);
    }
}