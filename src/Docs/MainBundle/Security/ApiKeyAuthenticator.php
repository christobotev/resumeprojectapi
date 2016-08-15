<?php
namespace Docs\MainBundle\Security;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Docs\MainBundle\Security\ApiKeyUserProvider;

/**
 * Api key authentication
 * @author hbotev
 *
 */
class ApiKeyAuthenticator implements AuthenticationFailureHandlerInterface, SimplePreAuthenticatorInterface
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface::createToken()
     */
    public function createToken(Request $request, $providerKey)
    {
        $apiKey = $request->get("apiKey");

        if (!$apiKey) {
            throw new BadCredentialsException("Api key is required");
        }

        return new PreAuthenticatedToken(
            "anon",
            $apiKey,
            $providerKey
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface::authenticateToken()
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                "The user provider must be an instance of ApiKeyUserProvider ("
                . get_class($userProvider) . " was given)."
            );
        }

        $apiKey = $token->getCredentials();

        if (!$apiKey) {
            throw new AuthenticationException("API Key '{$apiKey}' does not exist.");
        }

        $service = $userProvider->getServiceForApiKey($apiKey);

        if (!$service) {
            throw new AuthenticationException("Invalid api key");
        }

        $serviceData = new ServiceInstance($service);

        //$user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $serviceData,
            $apiKey,
            $providerKey,
            ["ROLE_REST"]
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface::supportsToken()
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return ($token instanceof PreAuthenticatedToken)
                && ($token->getProviderKey() === $providerKey);
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface::onAuthenticationFailure()
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        $serializer = new Serializer(
            array(new CustomNormalizer(), new GetSetMethodNormalizer()),
            array(new XmlEncoder())
        );

        return new Response(
            $serializer->serialize(
                [
                    "message" => $exception->getMessage(),
                    "status" => "failed"
                ],
                "xml"
            ),
            403
        );
    }
}
