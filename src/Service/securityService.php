<?php

namespace App\Service;

use App\Entity\Logs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class securityService
{


    public function __construct()
    {

    }

    public function verifyData($dataToVerifyInArray, $verifyIfIsset, $verifyIfIsEmpty, $typeToVerify, $namesOfDataToVerify)
    {
        foreach ($dataToVerifyInArray as $key => $value) {
            if ($verifyIfIsset ) {
                if(!isset($value)){
                    return     [
                        'state' => 'OK',
                        'value' => $namesOfDataToVerify[$key]
                    ];
                }
            }
            if($verifyIfIsEmpty){

            }
      }
    }
}