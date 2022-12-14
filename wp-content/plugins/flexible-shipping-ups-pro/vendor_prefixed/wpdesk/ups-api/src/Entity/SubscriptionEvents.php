<?php

namespace UpsProVendor\Ups\Entity;

class SubscriptionEvents
{
    public $Name;
    public $Number;
    public $SubscriptionStatus;
    public $DateRange;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->SubscriptionStatus = new \UpsProVendor\Ups\Entity\SubscriptionStatus();
        $this->DateRange = new \UpsProVendor\Ups\Entity\DateRange();
        if (null !== $response) {
            if (isset($response->Name)) {
                $this->Name = new $response->Name();
            }
            if (isset($response->Number)) {
                $this->Number = new $response->Number();
            }
            if (isset($response->SubscriptionStatus)) {
                $this->SubscriptionStatus = new \UpsProVendor\Ups\Entity\SubscriptionStatus($response->SubscriptionStatus);
            }
            if (isset($response->DateRange)) {
                $this->DateRange = new \UpsProVendor\Ups\Entity\DateRange($response->DateRange);
            }
        }
    }
}
