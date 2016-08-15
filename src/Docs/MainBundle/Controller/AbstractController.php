<?php
namespace Docs\MainBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Docs\MainBundle\View\ResponseView;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Doctrine\EntityInterface;
use Doctrine\Common\Collections\Criteria;
use Docs\CommonBundle\Doctrine\Repository\AbstractRepository;

abstract class AbstractController extends FOSRestController
{
    public function returnResponse($response, $code = 200)
    {
        $handler = $this->get("fos_rest.view_handler");
        /* @var $handler \FOS\RestBundle\View\ViewHandler */

        if (isset($response["result"]) && !isset($response['status'])) {
            $response['status'] = "success";
        }

        $responseView = new ResponseView();
        $responseView->setResult($response)
                     ->setStatusCode($code);

        return $handler->handle(new View($responseView, $code));
    }

    public function returnError($error, $code = 400)
    {
        $handler = $this->get("fos_rest.view_handler");
        /* @var $handler \FOS\RestBundle\View\ViewHandler */

        $responseView = new ResponseView();
        $responseView->setError(["message" => $error, "status" => "failed"])
                     ->setStatusCode($code);

        return $handler->handle(new View($responseView, $code));
    }

    /**
     * Return list of all entities
     */
    protected function listAll()
    {
        $result = $this->findAll();

        if ($result) {
            if (!is_array($result)) {
                if ($this->getRequest()->get("count")) {
                    $result = ['count' => $this->resultCount];
                }
                return $this->returnResponse(["result" => $result], 200);
            }

            $result['count'] = $this->resultCount;

            return $this->returnResponse(["result" => $result], 200);
        } else {
            return $this->returnResponse(["result" => ["count" => 0]], 200);
        }

    }

    /**
     * Return one entity by id
     * @param int $id
     */
    protected function findByID($id)
    {
        $result = $this->getById($id);

        if ($result) {
            return $this->returnResponse(
                ["result" => $result, "status" => "success"],
                200
            );
        } else {
            return $this->returnError("Not found", 404);
        }
    }

    /**
     * Create new entity
     * @param array $data
     * @return Response
     */
    protected function create(array $data)
    {
        if ($data instanceof EntityInterface) {
            $entity = $data;
        } else {
            $entity = $this->createEntity($data);
        }

        $valid = $this->validateEntity($entity);

        if ($valid instanceof Response) {
            return $valid;
        }

        if ($this->getRequest()->get("validate") == 1) {
            return $this->returnResponse(["message" => "Data is valid"], 200);
        }

        try {
            $this->saveEntity($entity);

            return $this->returnResponse(["result" => $entity], 201);

        } catch (\Exception $e) {
            return $this->returnError(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Update entity
     * @param int $id
     * @param array $data
     * @return Response
     */
    protected function updateEntity($id, array $data)
    {
        $entity = $this->getById($id);

        if (!$entity) {
            return $this->returnError(["message" => "Entity not found!"], 404);
        }

        $entity = $this->populateEntity($entity, $data);

        $valid = $this->validateEntity($entity);

        if ($valid instanceof Response) {
            return $valid;
        }

        if ($this->getRequest()->get("validate") == 1) {
            return $this->returnResponse(["message" => "Data is valid"], 200);
        }

        try {
            $this->saveEntity($entity);

            return $this->returnResponse(["result" => $entity, 201]);

        } catch (\Exception $e) {
            return $this->returnError(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Delete entity from the db
     * @param int $id
     * @return Response
     */
    protected function deleteEntity($id)
    {
        $entity = $this->getById($id);

        if (!$entity) {
            return $this->returnError(["message" => "Entity not found!"], 404);
        }

        try {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush($entity);
            return $this->returnResponse(["result" => "Entity deleted", 201]);

        } catch (\Exception $e) {
            return $this->returnError(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Validate entity
     * @param EntityInterface $entity
     * @return true|Response
     */
    protected function validateEntity(EntityInterface $entity)
    {
        $validator = $this->get("validator");

        $validationData = $this->get("validator")->validate($entity);

        if ($validationData->count() == 0) {
            return true;
        }

        $errors = [];
        foreach ($validationData as $error) {
            /* @var $error \Symfony\Component\Validator\ConstraintViolationInterface */
            $errors[$error->getPropertyPath()]["field"] = $error->getPropertyPath();
            $errors[$error->getPropertyPath()]["messages"][] = ["#" => $error->getMessage(), "@humanReadable" => 1];
        }

        return $this->returnError($errors, 412);

    }

    /**
     * Returns paged result
     *
     * @param \Docs\CommonBundle\Doctrine\Repository\AbstractRepository $repository
     * @param int $itemCountPerPage
     * @param int $offset
     * @param int $count
     * @param int $allItems
     * @return array
     */
    protected function getResult(AbstractRepository $repository, $itemCountPerPage, $offset, $count, $allItems)
    {
        $addition = $this->getAddition();

        if (!empty($addition)) {
            if (!empty($addition['conditions'])) {
                $repository->setWhere($addition['conditions']);
            }

            if (!empty($addition['order'])) {
                $repository->setOrder($addition['order']);
            }
        }

        // add default order to ensure consistency in the paginated results
        if (empty($addition) || empty($addition['order'])) {
            $repository = $this->addDefaultOrder($repository);
        }

        $this->addExtraConditionsToRepository($repository);
        $this->resultCount = $repository->getTotalCount();

        if ($count) {
            return $this->resultCount;
        }

        if ($allItems) {
            return $repository->load();
        }

        return $repository->setFirstResult($offset)->setMaxResults($itemCountPerPage)->load();
    }

    /**
     * Return entity manager
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager('default');
    }

    /**
     * Adds extra conditions to repository for searching through records
     *
     * @param Docs\CommonBundle\Doctrine\Repository\AbstractRepository $repository
     */
    protected function addExtraConditionsToRepository($repository)
    {
        $repository->addCriteria($this->getCriteria());
    }

    /**
     * Returns Addition array query parameter
     * @return array
     */
    protected function getAddition()
    {
        return $this->get('request_stack')->getCurrentRequest()->get('addition', []);
    }


    /**
     * Defines additional search criteria
     *
     * @return \Doctrine\Common\Collections\Criteria
     */
    protected function getCriteria()
    {
        return Criteria::create();
    }
    /**
     * Return all results
     * @return array
     */
    protected function findAll()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $repository = $this->getEntityManager()->getRepository($this->entityClass);
        $count = $request->get("count", 0);
        $allItems = $request->get("allItems", 0);
        $itemCountPerPage = $request->get('itemCountPerPage', 10);
        $offset = $request->get('offset', null);

        return $this->getResult($repository, $itemCountPerPage, $offset, $count, $allItems);
    }

    /**
     * Get entity by id
     * @param int $id
     * @return \Docs\CommonBundle\Doctrine\EntityInterface
     */
    protected function getById($id)
    {
        return $this->getEntityManager()
                        ->find($this->entityClass, (int) $id);
    }


    /**
     * Creates new entity
     *
     * @param string|array $data POST request data
     * @return object|mixed|null Query result
     */
    protected function createEntity(array $data = null)
    {
        $entity = new $this->entityClass();
        return $this->populateEntity($entity, $data);
    }

    /**
     * Populate entity with the given data
     * @param EntityInterface $entity
     * @param array $data
     * @return EntityInterface
     */
    protected function populateEntity(EntityInterface $entity, array $data = null)
    {
        $alias = explode("\\", $this->entityClass);
        $alias = end($alias);
        if (!empty($data[$alias]) && is_array($data[$alias])) {
            $entity->populateFromArray($data[$alias]);
        }

        return $entity;
    }

    /**
     * Persist entity to the database
     * @param EntityInterface $entity
     */
    protected function saveEntity(EntityInterface $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Add default order to the repository. the default order is
     * ascending and the default field is the entity's identifier
     * @param AbstractRepository $repository
     */
    protected function addDefaultOrder(AbstractRepository $repository)
    {
        $entityName = $repository->getClassName();
        $metaData = $this->getEntityManager()
                            ->getMetadataFactory()
                            ->getMetadataFor($entityName);

        $identifier = $metaData->getIdentifier();

        // nothing to do if the entity doesn't have an identifier
        if (empty($identifier)) {
            return $repository;
        }

        // if the entity has a composite primary key, get only the first field
        $identifier = array_shift($identifier);

        $repository->setOrder([["field" => $identifier, "direction" => "ASC"]]);

        return $repository;
    }

    /**
     * This method is called when the result from listAll() is
     * fetched. This can be used by child classes to transform
     * the result data before serialization
     * @param mixed $result
     * @return mixed
     */
    protected function handleRawListResult($result)
    {
        return $result;
    }

    /**
     * This method is called when the result from findByID() is
     * fetched. This can be used by child classes to transform
     * the result data before serialization
     * @param mixed $result
     * @return mixed
     */
    protected function handleRawSingleResult($result)
    {
        return $result;
    }
}
