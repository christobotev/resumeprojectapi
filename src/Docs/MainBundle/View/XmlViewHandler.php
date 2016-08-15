<?php
namespace Docs\MainBundle\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use Docs\MainBundle\View\ResponseView;
use JMS\Serializer\SerializationContext;
use Docs\MainBundle\Serializer\DocsXmlSerializationVisitor;

/**
 * Xml serialization
 * @author hbotev
 *
 */
class XmlViewHandler
{
    /**
     * Create docs formatted xml response
     * @param ViewHandler $handler
     * @param View $view
     * @param Request $request
     * @param string $format
     * @throws \InvalidArgumentException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request, $format)
    {
        if ($format != "xml") {
            throw new \InvalidArgumentException(
                "This handler supports only xml format"
            );
        }

        $data = $view->getData();

        if ($data instanceof ResponseView) {
            $statusCode = $data->getStatusCode();
            $dataContent = $data->isError() ? $data->getError() : $data->getResult();
            $context = $this->buildSerializationContext();
        } else {
            $statusCode = $view->getStatusCode();
            $dataContent = $data;
            $context = $this->buildSerializationContext();
        }

        if ($statusCode >= 400) {
            $serializedData = $this->serializeError($dataContent);
        } else {
            $serializedData = $this->serializeSuccess($dataContent, $context);
        }

        $response = new Response(
            $serializedData,
            $statusCode,
            $view->getHeaders()
        );

        return $response;
    }

    /**
     * Serialize error messages
     * @param mixed $data
     * @return string
     */
    protected function serializeError($data)
    {
        $serializer = new SymfonySerializer(
            [new CustomNormalizer(), new GetSetMethodNormalizer()],
            [new XmlEncoder()]
        );

        return $serializer->serialize($data, "xml");
    }

    /**
     * Serialize success data
     * @param mixed $data
     * @return string
     */
    protected function serializeSuccess($data, SerializationContext $context = null)
    {
        $namingStartegy = new IdenticalPropertyNamingStrategy();

        $visitor = new DocsXmlSerializationVisitor($namingStartegy);

        $visitor->setDefaultRootName("docs");

        $serializer = \JMS\Serializer\SerializerBuilder::create()
                        ->setPropertyNamingStrategy($namingStartegy)
                        ->setSerializationVisitor("xml", $visitor)
                        ->build();


        return $serializer->serialize($data, "xml", $context);
    }

    /**
     * Build the context object for the serializer
     * @return SerializationContext
     */
    protected function buildSerializationContext()
    {
        $context = new SerializationContext();
//         $context->setSerializeNull(true);

//         $context->setGroups("Index");
        return $context;
    }
}
