<?php


namespace App\Entity;


use App\Model\ApiModel;

interface EntityApiModel
{
    public static function fromModel(ApiModel $model);
}
