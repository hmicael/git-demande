<?php
// src/Controller/PersonneController.php

namespace App\Controller;

use App\Entity\Filleul;
use App\Entity\Parrain;
use App\Exception\MyException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @SWG\Tag(name="Parrain")
 */
class ParrainController extends PersonneController
{
    /**
     * Returns the list of Parrain order by user_id
     * @Rest\Get(
     *     path="/parrains",
     *     name="app_parrain_list",
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
     * @Rest\QueryParam(
     *     name="monthReport",
     *     requirements="^(?:[1-9]\d*|0)?(?:\.\d+)?$"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The list of Parrain according to user_id",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Parrain::class))
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
     *     name="monthReport",
     *     in="query",
     *     type="string",
     *     description="The current month total net sales"
     * )
     * @param Request $request
     * @param int $limit
     * @param int $offset
     * @return Response
     */
    public function index(Request $request, $limit = 20, $offset = 1)
    {
        $pager = $this->getDoctrine()
            ->getRepository(Parrain::class)
            ->getList($limit, $offset);
        return $this->showIndex($request, $pager);
    }

    /**
     * Returns the Parrain corresponding to the user_id
     * @ParamConverter("parrain", options={"mapping": {"userId": "userId"}})
     * @Rest\Get(
     *     path="/parrains/{userId}",
     *     name="app_parrain_show",
     *     requirements={"userId"="\d+"}
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="^(?:[1-9]\d*|0)?(?:\.\d+)?$"
     * )
     * @Rest\View
     * @SWG\Response(
     *     response=200,
     *     description="The Parrain corresponding to the user_id",
     *     @Model(type=Parrain::class)
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the Parrain"
     * )
     * @SWG\Parameter(
     *     name="monthReport",
     *     in="query",
     *     type="string",
     *     description="The current month total net sales"
     * )
     * @param Parrain $parrain
     * @return Response
     */
    public function show(Parrain $parrain)
    {
        return $this->showPersonne($parrain);
    }

    /**
     * Create a new Parrain
     * @Rest\Post(
     *     path="parrains/new",
     *     name="app_parrain_new"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("parrain", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="The created Parrain"
     * )
     * @SWG\Parameter(
     *     name="Parrain",
     *     in="body",
     *     @Model(type=Parrain::class),
     *     description="The Parrain to create"
     * )
     * @SWG\Parameter(
     *     name="monthReport",
     *     in="query",
     *     type="string",
     *     description="The current month total net sales"
     * )
     * @param Parrain $parrain
     * @param ConstraintViolationList $violations
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function new(Parrain $parrain, ConstraintViolationList $violations)
    {
        if (count($violations) > 0) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new MyException($message);
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($parrain);
            $em->flush();
            return $this->view(
                $parrain,
                Response::HTTP_CREATED,
                ['Location' => $this->generateUrl(
                    'app_parrain_show',
                    ['userId' => $parrain->getUserId(), UrlGeneratorInterface::ABSOLUTE_URL]
                )]
            );
        }
    }

    /**
     * Delete a Parrain
     * @ParamConverter("parrain", options={"mapping": {"userId": "userId"}})
     * @Rest\Delete(
     *     path="/parrains/{userId}",
     *     name="app_parrain_delete",
     *     requirements={"userId"="\d+"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Parrain is deleted"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the Parrain to delete"
     * )
     * @Rest\View(StatusCode=200)
     * @param Parrain $parrain
     * @return \FOS\RestBundle\View\View
     */
    public function delete(Parrain $parrain)
    {
        return $this->deletePersonne($parrain);
    }

    /**
     * Add a new Filleul
     * @Rest\Post(
     *     path="parrains/{userId}/new-filleul",
     *     name="app_parrain_newFilleul",
     *     requirements={"userId"="\d*"}
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("filleul", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="The created Filleul"
     * )
     * @SWG\Parameter(
     *     name="Filleul",
     *     in="body",
     *     @Model(type=Filleul::class),
     *     description="The Filleul to create"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the Parrain"
     * )
     * @param $userId
     * @param Filleul $filleul
     * @param ConstraintViolationList $violations
     * @param \App\Mailer\NotificationMailer $mailer
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function addFilleul($userId, Filleul $filleul, ConstraintViolationList $violations, \App\Mailer\NotificationMailer $mailer)
    {
        if (count($violations) > 0) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new MyException($message);
        } else {
            $em = $this->getDoctrine()->getManager();
            $toEmail = $this->container->getParameter('admin_email');
            $object = 'New direct customer';
            $parrain = $em->getRepository(Parrain::class)->findOneByUserId($userId);
            // if the filleul have a parrain (direct inscription case)
            if ($parrain !== null) {
                $parrain->addFilleul($filleul);
                $em->merge($parrain);
                $toEmail = $parrain->getEmail();
                $object = 'New Filleul';
            }
            $em->persist($filleul);
            $em->flush();
            $mailer->sendNotification($filleul, $object, $toEmail, $this->container->getParameter('admin_email'));
            return $this->view(
                $filleul,
                Response::HTTP_CREATED,
                ['Location' => $this->generateUrl(
                    'app_filleul_show',
                    ['userId' => $filleul->getUserId(), UrlGeneratorInterface::ABSOLUTE_URL]
                )]
            );
        }
    }

    /**
     * Assign a Filleul to a Parrain
     * @ParamConverter("parrain", options={"mapping": {"parrainUserId": "userId"}})
     * @ParamConverter("filleul", options={"mapping": {"filleulUserId": "userId"}})
     * @Rest\Put(
     *     path="parrains/{parrainUserId}/filleuls/{filleulUserId}/assign",
     *     name="app_parrain_assign",
     *     requirements={"parrainUserId"="\d+", "filleulUserId"="\d+"}
     * )
     * @Rest\View(StatusCode=200)
     * @SWG\Response(
     *     response=200,
     *     description="Filleul is successfully assigned to parrain"
     * )
     * @SWG\Parameter(
     *     name="parrainUserId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the Parrain"
     * )
     * @SWG\Parameter(
     *     name="filleulUserId",
     *     in="path",
     *     type="integer",
     *     description="The user_id of the Filleul"
     * )
     * @param Parrain $parrain
     * @param Filleul $filleul
     * @param \App\Mailer\NotificationMailer $mailer
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function assginFilleulToParrain(Parrain $parrain, Filleul $filleul, \App\Mailer\NotificationMailer $mailer)
    {
        if ($filleul->getParrain() != null) {
            throw new MyException("The Filleul already has a Parrain");
        }
        $em = $this->getDoctrine()->getManager();
        $parrain->addFilleul($filleul);
        $filleul->setIndirect(true);
        $em->merge($parrain);
        $em->merge($filleul);
        $em->flush();
        $mailer->sendNotification($filleul, 'A filleul is assigned to you', $parrain->getEmail(), $this->container->getParameter('admin_email'));
        return $this->view(
            ['message' => 'Filleul is successfully assigned to parrain'],
            Response::HTTP_OK
        );
    }

    /**
     * Send an email to invite user email provided
     * @Rest\Post(
     *     path="parrains/invite",
     *     name="app_parrain_invite"
     * )
     * @Rest\View(StatusCode=200)
     * @SWG\Parameter(
     *     name="emails",
     *     in="body",
     *     @SWG\Schema(
     *         type="string"
     *     ),
     *     description="List of email to send an invitation "
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Emails are send"
     * )
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param ValidatorInterface $validator
     * @return \FOS\RestBundle\View\View
     * @throws MyException
     */
    public function invite(Request $request, \Swift_Mailer $mailer, ValidatorInterface $validator)
    {
        $body = $request->getContent();
        $body = json_decode($body, true);
        $emails = $body['emails'];
        $em = $this->getDoctrine()->getManager();
        $parrain = $em->getRepository(Parrain::class)->findOneByUserId($body['parrain']['user_id']);
        if (null == $parrain) { // if the parrain doesn't already exists, it'll be created
            $parrain = new Parrain();
            $parrain->setUserId($body['parrain']['user_id']);
            $parrain->setUserName($body['parrain']['user_name']);
            $parrain->setEmail($body['parrain']['email']);
            $em->persist($parrain);
            $em->flush();
        }
        $emailConstraint = new Assert\Email();
        $message = (new \Swift_Message('Invitation !'))
            ->setFrom($parrain->getEmail());
        foreach ($emails as $email) {
            // use the validator to validate the value
            $errors = $validator->validate(
                $email,
                $emailConstraint
            );
            if (count($errors) > 0) {
                $emailConstraint->message = 'Email ' . $email . ' isn\'t a valid email';
                throw new MyException($emailConstraint->message);
            } else {
                $message->setBody(
                    $this->renderView(
                        'InvitationMail.html.twig',
                        [
                            'parrain_user_id' => $parrain->getUserId(),
                            'parrain_user_name' => $parrain->getUserName(),
                            'parrain_email' => $parrain->getEmail(),
                            'your_email' => $email
                        ]
                    ),
                    'text/html'
                )
                    ->setTo($email);
                $mailer->send($message);
            }
        }
        return $this->view(
            ['message' => 'Emails are send'],
            Response::HTTP_OK
        );
    }
}
