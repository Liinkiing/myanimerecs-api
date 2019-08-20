<?php


namespace App\Message;


class AddInexistingAnimes implements AsyncMessage
{
    protected $inexistingMalIds;

    public function __construct(array $malIds)
    {
        $this->inexistingMalIds = $malIds;
    }

    public function getInexistingMalIds(): array
    {
        return $this->inexistingMalIds;
    }

}
