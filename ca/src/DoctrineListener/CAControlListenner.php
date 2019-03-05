<?php
// src/DoctrineListener/CAControlListenner.php

namespace App\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Orders;
use App\Entity\CA;

class CAControlListenner
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $order = $args->getObject();
        $em = $args->getObjectManager();
        if (!$order instanceof Orders) {
            return;
        }
        $date = date_format($order->getDateCompleted(), "Y-m");
        $userId = $order->getUserId();
        $CA = $em->getRepository(CA::class)
            ->findByYearMonth($userId, $date."%")
        ;
        if (null == $CA) {
            $newDate = date_create($date."-01");
            $CA = new CA($order->getUserId(), $newDate);
            $CA->addOrder($order);
            $em->persist($CA);
            $em->flush();
        } else {
            $CA->addOrder($order);
            $CA->setUpdatedAt(new \Datetime());
            $em->persist($CA);
            $em->flush();
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $order = $args->getObject();
        $em = $args->getObjectManager();
        if (!$order instanceof Orders) {
            return;
        }
        $CA = $order->getCA();
        $CA->removeOrder($order);
        $CA->setUpdatedAt(new \Datetime());
        $em->persist($CA);
        $em->flush();
    }
}
