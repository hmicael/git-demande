<?php
// src/Controller/CAController.php
namespace App\Controller;

use App\Entity\CA;
use App\Entity\Orders;
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
use Symfony\Component\Validator\ConstraintViolationList;

class CAController extends FOSRestController
{
    /**
     * Returns the current year CA list of the corresponding user
     * @Rest\Get(
     *     path="/cas/{userId}",
     *     name="app_ca_list",
     *     requirements={"userId"="\d+"}
     * )
     * @Rest\QueryParam(
     *     name="year",
     *     requirements="^[0-9]{4}$",
     *     default="2019"
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
     *     description="The given year CA list of the corresponding user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=CA::class))
     *     )
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the user"
     * )
     * @SWG\Parameter(
     *     name="year",
     *     in="query",
     *     type="string",
     *     description="A specified year"
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
     * @param $userId
     * @param $year
     * @param $limit
     * @param $offset
     * @return Response
     */
    public function index(Request $request, $userId = 1, $year = 2019, $limit = 20, $offset = 1)
    {
        $response = new Response();
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $pager = $this->getDoctrine()
            ->getRepository(CA::class)
            ->getList($userId, $year . "%", $limit, $offset);
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
            new HRoute('app_ca_list', array())
        );
        $data = $this->get('jms_serializer')->serialize($paginatedCollection, 'json');
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Create a new Orders
     * @Rest\Post(
     *     path="orders/new",
     *     name="app_order_new"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("order", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="The created Orders"
     * )
     * @SWG\Parameter(
     *     name="Demande",
     *     in="body",
     *     @Model(type=Orders::class),
     *     description="The Orders to create"
     * )
     * @param Orders $order
     * @param ConstraintViolationList $violations
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function new(Orders $order, ConstraintViolationList $violations)
    {
        if (count($violations) > 0) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new MyException($message);
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();
            return $this->view(Response::HTTP_CREATED);
        }
    }

    /**
     * Delete an Orders
     * @Rest\Delete(
     *     path="/orders/{orderId}",
     *     name="app_order_delete",
     *     requirements={"orderId"="\d+"}
     * )
     * @Rest\View(StatusCode=200)
     * @SWG\Response(
     *     response=200,
     *     description="Orders is deleted"
     * )
     * @SWG\Parameter(
     *     name="orderId",
     *     in="path",
     *     type="integer",
     *     description="The order_id of the Orders to delete"
     * )
     * @ParamConverter("order", options={"order_id" = "orderId"})
     * @param Orders $orders
     */
    public function delete(Orders $orders)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($orders);
        $em->flush();
        return $this->view(
            ['message' => sprintf('Order %s is successfully deleted', $orders->getOrderId())],
            Response::HTTP_OK
        );
    }

    /**
     * Returns a specified CA according to the user, the year, and the month
     * @Rest\Get(
     *     path="/cas/{userId}/{year}/{month}",
     *     name="app_ca_show",
     *     requirements={"userId"="\d+", "year"="^[0-9]{4}$", "month"="^[0-9]{2}$"}
     * )
     * @Rest\View
     * @SWG\Response(
     *     response=200,
     *     description="A specified CA according to the user, the year, and the month",
     *     @Model(type=CA::class)
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the user"
     * )
     * @SWG\Parameter(
     *     name="year",
     *     in="path",
     *     type="integer",
     *     description="A specified year"
     * )
     * @SWG\Parameter(
     *     name="month",
     *     in="path",
     *     type="integer",
     *     description="A specified month"
     * )
     * @param $userId
     * @param int $year = 2018
     * @param $month
     * @return Response
     * @throws MyException
     */
    public function show(Request $request, $userId, $month, $year = 2018)
    {
        $response = new Response();
        $response->setSharedMaxAge(120);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $em = $this->getDoctrine()->getManager();
        $CA = $em->getRepository(CA::class)->findByYearMonth($userId, $year . '-' . $month . '%');
        if (null === $CA) {
            throw new MyException("CA is not found");
        }
        $response->setLastModified($CA->getUpdatedAt());
        if ($response->isNotModified($request)) {
            return $response;
        }
        $data = $this->get('jms_serializer')->serialize($CA, 'json');
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
