<?php
// src/DoctrineListener/ParrainControlListenner.php

namespace App\DoctrineListener;

use App\Entity\Parrain;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\RequestStack;

class ParrainControlListenner
{
    private $request;
    private $client;
    private $serializer;
    private $seuilCAMensuel;
    private $seuilNombreFilleuls;
    private $pourcentageCAMembreMensuel;
    private $pourcentageCAFilleulDirect;
    private $pourcentageCAFilleulIndirect;

    public function __construct(
        RequestStack $request,
        Client $client,
        Serializer $serializer,
        $seuilCAMensuel,
        $seuilNombreFilleuls,
        $pourcentageCAMembreMensuel,
        $pourcentageCAFilleulDirect,
        $pourcentageCAFilleulIndirect
    )
    {
        $this->request = $request->getCurrentRequest();
        $this->client = $client;
        $this->serializer = $serializer;
        $this->seuilCAMensuel = $seuilCAMensuel;
        $this->seuilNombreFilleuls = $seuilNombreFilleuls;
        $this->pourcentageCAMembreMensuel = $pourcentageCAMembreMensuel;
        $this->pourcentageCAFilleulDirect = $pourcentageCAFilleulDirect;
        $this->pourcentageCAFilleulIndirect = $pourcentageCAFilleulIndirect;
    }


    /**
     * Function called after a parrain entity is loaded from database
     * @param LifecycleEventArgs $args
     * @return array|void
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $parrain = $args->getObject();
        $route = $this->request->get('_route');
        $route = preg_replace_callback(
            '#^app_(.*)_(.*)$#',
            function ($matches) {
                return $matches[1];
            },
            $route
        );
        if (!$parrain instanceof Parrain ||
            $this->request->getMethod() == 'DELETE' ||
            $route == 'filleul') {
            return;
        }
        $uri = 'cas/' . $parrain->getUserId() . '/' . date('Y/m');
        try {
            $response = $this->client->get($uri);
            $CA = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');
            $bonus = 0;
            $parrain->setBonus($bonus);
            // if the user's CA is >= the mensual sill and if he has enough filleuls
            if ($CA['total'] >= $this->seuilCAMensuel ||
                count($parrain->getFilleuls()) >= $this->seuilNombreFilleuls
            ) {
                $monthReport = (float)$this->request->get('monthReport');
                // if the percentage of the user's CA is >= the mensual percentage
                if ((($CA['total'] * 100) / $monthReport) >= $this->pourcentageCAMembreMensuel) {
                    $bonus += 1; // set some bonus
                    // get the different filleul's CA
                    $filleuls = $parrain->getDifferentFilleuls();
                    // verify if the direct filleul's CA is >= the sill
                    $directFilleulCA = $this->getFilleulsCA($filleuls['direct']);
                    if ((($directFilleulCA * 100) / $monthReport) >= $this->pourcentageCAFilleulDirect) {
                        $bonus += 1; // set some bonus
                    } else {
                        $bonus += 0.5; // set some bonus
                    }
                    // verify if the indirect filleul's CA is >= the sill
                    $indirectFilleulCA = $this->getFilleulsCA($filleuls['indirect']);
                    if ((($indirectFilleulCA * 100) / $monthReport) >= $this->pourcentageCAFilleulIndirect) {
                        $bonus += 1; // set some bonus
                    } else {
                        $bonus += 0.5; // set some bonus
                    }
                    $parrain->setBonus($bonus);
                }
            }
        } catch (\Exception $e) {
            $parrain->setBonus('Bonus is unavailable');
            return ['error' => 'Bonus is unavailable'];
        }
        return;
    }

    /**
     * Function which get the Filleul's CA
     * @param $filleuls
     * @return int
     */
    public function getFilleulsCA($filleuls)
    {
        $GLOBALS['results'] = 0;
        $requests = function ($filleuls) {
            foreach ($filleuls as $filleul) {
                $uri = 'cas/' . $filleul->getUserId() . '/' . date('Y/m');
                yield new Request('GET', $uri);
            }
        };
        $pool = new Pool($this->client, $requests($filleuls), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) {
                // this is delivered each successful response
                $response = $response->getBody()->getContents();
                $response = $this->serializer->deserialize($response, 'array', 'json');
                $GLOBALS['results'] += $response['total'];
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request or filleul doesn't yet have a CA
                //throw new \Exception("Unable to response for request number " . $index);
            },
        ]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();
        return $GLOBALS['results'];
    }
}
