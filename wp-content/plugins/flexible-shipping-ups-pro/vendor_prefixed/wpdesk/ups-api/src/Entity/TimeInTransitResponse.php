<?php

namespace UpsProVendor\Ups\Entity;

class TimeInTransitResponse
{
    /**
     * @var
     */
    public $PickupDate;
    /**
     * @var AddressArtifactFormat
     */
    public $TransitFrom;
    /**
     * @var AddressArtifactFormat
     */
    public $TransitTo;
    /**
     * @var
     */
    public $DocumentsOnlyIndicator;
    /**
     * @var
     */
    public $AutoDutyCode;
    /**
     * @var ShipmentWeight
     */
    public $ShipmentWeight;
    /**
     * @var Charges
     */
    public $InvoiceLineTotal;
    /**
     * @var
     */
    public $Disclaimer;
    /**
     * @var array
     */
    public $ServiceSummary;
    /**
     * @var
     */
    public $MaximumListSize;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->TransitFrom = new \UpsProVendor\Ups\Entity\Address();
        $this->TransitTo = new \UpsProVendor\Ups\Entity\Address();
        $this->ShipmentWeight = new \UpsProVendor\Ups\Entity\ShipmentWeight();
        $this->InvoiceLineTotal = new \UpsProVendor\Ups\Entity\Charges();
        $this->ServiceSummary = [];
        if (null !== $response) {
            if (isset($response->PickupDate)) {
                $this->PickupDate = $response->PickupDate;
            }
            if (isset($response->TransitFrom->AddressArtifactFormat)) {
                $this->TransitFrom = new \UpsProVendor\Ups\Entity\AddressArtifactFormat($response->TransitFrom->AddressArtifactFormat);
            }
            if (isset($response->TransitTo->AddressArtifactFormat)) {
                $this->TransitTo = new \UpsProVendor\Ups\Entity\AddressArtifactFormat($response->TransitTo->AddressArtifactFormat);
            }
            if (isset($response->DocumentsOnlyIndicator)) {
                $this->DocumentsOnlyIndicator = $response->DocumentsOnlyIndicator;
            }
            if (isset($response->AutoDutyCode)) {
                $this->AutoDutyCode = $response->AutoDutyCode;
            }
            if (isset($response->ShipmentWeight)) {
                $this->ShipmentWeight = new \UpsProVendor\Ups\Entity\ShipmentWeight($response->ShipmentWeight);
            }
            if (isset($response->InvoiceLineTotal)) {
                $this->InvoiceLineTotal = new \UpsProVendor\Ups\Entity\Charges($response->InvoiceLineTotal);
            }
            if (isset($response->Disclaimer)) {
                $this->Disclaimer = $response->Disclaimer;
            }
            if (isset($response->ServiceSummary)) {
                foreach ($response->ServiceSummary as $serviceSummary) {
                    $this->ServiceSummary[] = new \UpsProVendor\Ups\Entity\ServiceSummary($serviceSummary);
                }
            }
            if (isset($response->MaximumListSize)) {
                $this->MaximumListSize = $response->MaximumListSize;
            }
        }
    }
}
