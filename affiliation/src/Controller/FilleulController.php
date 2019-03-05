<?php
// src/Controller/FilleulController.php

namespace App\Controller;

use App\Entity\Filleul;
use App\Entity\Parrain;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SWG\Tag(name="Filleul")
 */
class FilleulController extends PersonneController
{
    /**
     * Returns the list of Filleul of one Parrain order by user_id
     * @ParamConverter("parrain", options={"mapping": {"parrain": "userId"}})
     * @Rest\Get(
     *     path="/filleuls/{parrain}/list",
     *     name="app_filleul_list",
     *     requirements={"parrain"="\d+"}
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
     *     description="The list of Filleul according to user_id",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Filleul::class))
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
     * @SWG\Parameter(
     *     name="parrain",
     *     in="path",
     *     type="integer",
     *     description="The userId of the parrain"
     * )
     * @param Request $request
     * @param Parrain $parrain
     * @param int $limit
     * @param int $offset
     * @return Response
     */
    public function index(Request $request, Parrain $parrain, $limit = 20, $offset = 1)
    {
        $pager = $this->getDoctrine()
            ->getRepository(Filleul::class)
            ->getList($parrain, $limit, $offset);
        return $this->showIndex($request, $pager, ['parrain' => $parrain->getUserId()]);
    }

    /**
     * Returns the list of Filleul who doesn't have a parrain (from direct inscription)
     * @Rest\Get(
     *     path="/filleuls/indirect/list",
     *     name="app_filleul_indirect_list"
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
     *     description="The list of Filleul according to user_id",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Filleul::class))
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
    public function indirectFilleulList(Request $request, $limit = 20, $offset = 1)
    {
        $pager = $this->getDoctrine()
            ->getRepository(Filleul::class)
            ->getFilleulIndirectList($limit, $offset);
        return $this->showIndex($request, $pager);
    }

    /**
     * Returns the Filleul corresponding to the id
     * @ParamConverter("filleul", options={"mapping": {"userId": "userId"}})
     * @Rest\Get(
     *     path="/filleuls/{userId}",
     *     name="app_filleul_show",
     *     requirements={"userId"="\d+"}
     * )
     * @Rest\View
     * @SWG\Response(
     *     response=200,
     *     description="The Filleul corresponding to the id",
     *     @Model(type=filleul::class)
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The userId of the Filleul"
     * )
     * @param filleul $filleul
     * @return Response
     */
    public function show(Filleul $filleul)
    {
        return $this->showPersonne($filleul);
    }

    /**
     * Delete a Filleul
     * @ParamConverter("filleul", options={"mapping": {"userId": "userId"}})
     * @Rest\Delete(
     *     path="/filleuls/{userId}",
     *     name="app_filleul_delete",
     *     requirements={"userId"="\d+"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Filleul is deleted"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The userId of the Filleul to delete"
     * )
     * @Rest\View(StatusCode=200)
     * @param Filleul $filleul
     * @return \FOS\RestBundle\View\View
     */
    public function delete(Filleul $filleul)
    {
        return $this->deletePersonne($filleul);
    }
}
