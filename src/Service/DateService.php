<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DateService
{
    private $tokenStorage;
    private $association = [
        "UE" => "d/m/Y",
        "SUI" => "d.m.Y",
        "PB" => "d-m-Y",
        "US" => "m/d/Y",
        "AS" => "Y/m/d",
        "ISO" => "Y-m-d",
    ];
    private $associationKey = [
        "UE", "SUI", "PB", "US", "AS", "ISO"
    ];

    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->user = $this->tokenStorage->getToken()->getUser();
    }

    public function formateDate($date)
    {
        if (!$date) {
            return null;
        }
        $dataFormat = $this->user->getSetting()->getDateFormat();
        if (!in_array($dataFormat, $this->associationKey)) {
            return $date->format('d/m/Y');
        }
        return $date->format($this->association[$dataFormat]);

    }

    public function formateDateWithHour($date)
    {
        if ($date) {
            return null;
        }
        $dataFormat = $this->user->getSetting()->getDateFormat();
        if (!in_array($dataFormat, $this->associationKey)) {
            return $date->format('d/m/Y H:i');
        }
        return $date->format($this->association[$dataFormat] . ' H:i');

    }
}