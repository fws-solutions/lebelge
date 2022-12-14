<?php

namespace UpsProVendor\Ups\Entity;

/**
 * @author Eduard Sukharev <eduard.sukharev@opensoftdev.ru>
 */
class BillShipper
{
    /**
     * @var string
     */
    private $accountNumber;
    /**
     * @var CreditCard
     */
    private $creditCard;
    /**
     * @param \stdClass|null $attributes
     */
    public function __construct(\stdClass $attributes = null)
    {
        if (isset($attributes->AccountNumber)) {
            $this->setAccountNumber($attributes->AccountNumber);
        }
        if (isset($attributes->CreditCard)) {
            $this->setAccountNumber(new \UpsProVendor\Ups\Entity\CreditCard($attributes->CreditCard));
        }
    }
    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    /**
     * @param string $accountNumber
     *
     * @return BillShipper
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
    /**
     * @return CreditCard
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }
    /**
     * @param CreditCard $creditCard
     * @return BillShipper
     */
    public function setCreditCard(\UpsProVendor\Ups\Entity\CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }
}
