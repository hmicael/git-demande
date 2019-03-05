<?php
// src/Controller/DemandeController.php

namespace App\Controller;

use App\Entity\Demande;
use App\Exception\MyException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Hateoas\Configuration\Route as HRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class DemandeController extends FOSRestController
{

    /**
     * Returns the list of Demande according to the date of submission
     * @Rest\Get(
     *     path="/demandes",
     *     name="app_demande_list",
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="20"
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="1"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The list of Demande according to the date of submission",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Demande::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="Max number per page"
     * )
     * @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     type="integer",
     *     description="The index of the element by which one begins"
     * )
     * @param Request $request
     * @param int $limit
     * @param int $offset
     * @return Response
     */
    public function index(Request $request, $limit = 20, $offset = 1)
    {
        $response = new Response();
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('proxy-revalidate', true);
        $pager = $this->getDoctrine()
            ->getRepository(Demande::class)
            ->getList($limit, $offset);
        $etag = md5(serialize($pager->getCurrentPageResults()));
        $response->setEtag($etag);
        // Check that the Response is not modified for the given Request
        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }
        $pagerfantaFactory = new PagerfantaFactory();
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new HRoute('app_demande_list', array())
        );
        $data = $this->get('jms_serializer')->serialize($paginatedCollection, 'json');
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Returns the Demande corresponding to the id
     * @Rest\Get(
     *     path="/demandes/{id}",
     *     name="app_demande_show",
     *     requirements={"id"="\d+"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The Demande corresponding to the id",
     *     @Model(type=Demande::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the Demande"
     * )
     * @Rest\View
     * @param Demande $demande
     * @return Response
     */
    public function show(Demande $demande)
    {
        $data = $this->get('jms_serializer')->serialize($demande, 'json');
        $response = new Response($data);
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        return $response;
    }

    /**
     * Create a new Demande
     * @Rest\Post(
     *     path="demandes/new",
     *     name="app_demande_new"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("demande", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="The created Demande"
     * )
     * @SWG\Parameter(
     *     name="Demande",
     *     in="body",
     *     @Model(type=Demande::class),
     *     description="The Demande to create"
     * )
     * @param Demande $demande
     * @param ConstraintViolationList $violations
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function new(Demande $demande, ConstraintViolationList $violations)
    {
        if (count($violations) > 0) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new MyException($message);
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($demande);
            $em->flush();
            return $this->view(
                $demande,
                Response::HTTP_CREATED,
                ['Location' => $this->generateUrl(
                    'app_demande_show',
                    ['id' => $demande->getId(), UrlGeneratorInterface::ABSOLUTE_URL]
                )]
            );
        }
    }

    /**
     * Toggle the state of a Demande
     * @Rest\Put(
     *     path="/demandes/{id}/toggle-state",
     *     name="app_demande_toogle_state",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(StatusCode=200)
     * @SWG\Response(
     *     response=200,
     *     description="Demande's state is toggled"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the Demande "
     * )
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function toggleState($id)
    {
        $em = $this->getDoctrine()->getManager();
        $demande = $em->getRepository(Demande::class)->find($id);
        if (null == $demande) {
            throw new NotFoundHttpException("Demande id ".$id." is not found.");

        }
        $state = null;
        if ($demande->getState() == true) {
            $demande->setState(false);
            $state = 'not yet validated';
        } else {
            $demande->setState(true);
            $state = 'successfully validated';
        }
        $em->flush();
        return $this->view(
            ['message' => 'Demande ' . $state],
            Response::HTTP_OK,
            ['Location' => $this->generateUrl(
                'app_demande_show',
                [
                    'id' => $demande->getId(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ]
            )]
        );
    }

    /**
     * Delete a Demande
     * @Rest\Delete(
     *     path="/demandes/{id}",
     *     name="app_demande_delete",
     *     requirements={"id"="\d+"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Demande is deleted"
     * )
     * @SWG\Parameter(
     *     name="Id",
     *     in="path",
     *     type="integer",
     *     description="The id of the Demande to delete"
     * )
     * @Rest\View(StatusCode=200)
     * @param Demande $demande
     */
    public function delete(Demande $demande)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($demande);
        $em->flush();
        return $this->view(
            ['message' => 'Demande is successfully deleted'],
            Response::HTTP_OK
        );
    }

    /**
     * Returns the number of Demande
     * @Rest\Get(
     *     path="/demandes/count",
     *     name="app_demande_count"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The number ofDemande"
     * )
     * @Rest\View
     */
    public function count()
    {
        $em = $this->getDoctrine()->getManager();
        $count = $em->getRepository(Demande::class)->count(["state" => false]);
        return new Response($count);
    }
}
