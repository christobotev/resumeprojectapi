<?php
namespace Docs\MainBundle\Controller;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default exception controller
 * @author hbotev
 *
 */
class ExceptionController extends AbstractController
{
    private $productionNonHumanReadable = 'This resource is not allowed!';

    /**
     * Handle exceptions
     * @param Request $request
     * @param unknown $exception
     * @param DebugLoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        $rfl = new \ReflectionClass($exception->getClass());

        if ($rfl->implementsInterface("\Docs\MainBundle\Exception\HumanReadableInterface")) {
            $isHumanReadable = 1;
        } else {
            $isHumanReadable = 0;
        }

        if (!in_array($this->container->get( 'kernel' )->getEnvironment(), array('dev', 'test'))
            && !$isHumanReadable) {
            $errorData = [
                "message" => [
                "@humanReadable" => $isHumanReadable,
                "#" => $this->productionNonHumanReadable
                ],
                "status" => "failed"
            ];
        } else {
            $errorData = [
                "message" => [
                    "@humanReadable" => $isHumanReadable,
                    "#" => $exception->getMessage()
                ],
                "status" => "failed"
            ];
        }

        $serializer = new Serializer(
            array(new CustomNormalizer(), new GetSetMethodNormalizer()),
            array(new XmlEncoder())
        );

        return new Response(
            $serializer->serialize(
                $errorData,
                "xml",
                array("xml_root_node_name" => "docs")
            ),
            $exception->getStatusCode(),
            ["Content-Type" => "application/xml"]
        );
    }
}
