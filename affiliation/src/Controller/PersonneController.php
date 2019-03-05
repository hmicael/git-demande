<?php
// src/Controller/PersonneController.php

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Hateoas\Configuration\Route as HRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class PersonneController extends FOSRestController
{
    /**
     * @param $personne
     * @return Response
     */
    protected function showPersonne($personne)
    {
        $data = $this->get('jms_serializer')->serialize($personne, 'json');
        $response = new Response($data);
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        return $response;
    }

    /**
     * @param Request $request
     * @param $pager
     * @return Response
     */
    protected function showIndex(Request $request, $pager, $routeParam = [])
    {
        $response = new Response();
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('proxy-revalidate', true);
        $etag = md5(serialize($pager->getCurrentPageResults()));
        $response->setEtag($etag);
        // Check that the Response is not modified for the given Request
        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }
        $route = $request->get('_route'); // get the route name
        $pagerfantaFactory = new PagerfantaFactory();
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new HRoute($route, $routeParam)
        );
        $data = $this->get('jms_serializer')->serialize($paginatedCollection, 'json');
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @param $personne
     * @return \FOS\RestBundle\View\View
     */
    protected function deletePersonne($personne)
    {
        $em = $this->getDoctrine()->getManager();
        $className = $em->getClassMetadata(get_class($personne))->getName();
        $className = substr($className, 11);
        $em->remove($personne);
        $em->flush();
        return $this->view(
            ['message' => sprintf('%s is successfully deleted', $className)],
            Response::HTTP_OK
        );
    }
}
