<?php

namespace UpsProVendor\Ups\Entity;

class ActivityLocation
{
    public $AddressArtifactFormat;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->AddressArtifactFormat = new \UpsProVendor\Ups\Entity\AddressArtifactFormat();
        if (null !== $response) {
            if (isset($response->AddressArtifactFormat)) {
                $this->AddressArtifactFormat = new \UpsProVendor\Ups\Entity\AddressArtifactFormat($response->AddressArtifactFormat);
            }
        }
    }
}
