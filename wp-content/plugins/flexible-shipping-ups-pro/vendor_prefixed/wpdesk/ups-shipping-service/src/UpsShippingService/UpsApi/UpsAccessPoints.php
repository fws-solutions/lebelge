<?php

/**
 * UPS implementation: UpsAccessPoints class.
 *
 * @package WPDesk\UpsShippingService\UpsApi
 */
namespace UpsProVendor\WPDesk\UpsShippingService\UpsApi;

use Psr\Log\LoggerInterface;
use UpsProVendor\Ups\Entity\AccessPointSearch;
use UpsProVendor\Ups\Entity\AddressKeyFormat;
use UpsProVendor\Ups\Entity\LocationSearchCriteria;
use UpsProVendor\Ups\Entity\LocatorRequest;
use UpsProVendor\Ups\Entity\OriginAddress;
use UpsProVendor\Ups\Entity\UnitOfMeasurement;
use UpsProVendor\Ups\Locator;
use UpsProVendor\WPDesk\AbstractShipping\CollectionPoints\CollectionPoint;
use UpsProVendor\WPDesk\AbstractShipping\CollectionPointCapability\CollectionPointsProvider;
use UpsProVendor\WPDesk\AbstractShipping\Exception\CollectionPointNotFoundException;
use UpsProVendor\WPDesk\AbstractShipping\Shipment\Address;
/**
 * Provides UPS access points as Collection Points.
 */
class UpsAccessPoints implements \UpsProVendor\WPDesk\AbstractShipping\CollectionPointCapability\CollectionPointsProvider
{
    /**
     * Access key.
     *
     * @var string
     */
    private $access_key;
    /**
     * User id.
     *
     * @var string
     */
    private $user_id;
    /**
     * Password.
     *
     * @var string
     */
    private $password;
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * UpsAccessPoints constructor.
     *
     * @param string          $access_key .
     * @param string          $user_id .
     * @param string          $password .
     * @param LoggerInterface $logger Logger.
     */
    public function __construct($access_key, $user_id, $password, \Psr\Log\LoggerInterface $logger)
    {
        $this->access_key = $access_key;
        $this->user_id = $user_id;
        $this->password = $password;
        $this->logger = $logger;
    }
    /**
     * Search access points.
     *
     * @param AddressKeyFormat  $address_key_format .
     * @param AccessPointSearch $access_point_search .
     * @param int               $maximum_list_size .
     *
     * @return \stdClass
     * @throws \Exception .
     */
    private function search_access_points(\UpsProVendor\Ups\Entity\AddressKeyFormat $address_key_format, \UpsProVendor\Ups\Entity\AccessPointSearch $access_point_search, $maximum_list_size = 50)
    {
        $locator_request = new \UpsProVendor\Ups\Entity\LocatorRequest();
        $origin_address = new \UpsProVendor\Ups\Entity\OriginAddress();
        $origin_address->setAddressKeyFormat($address_key_format);
        $locator_request->setOriginAddress($origin_address);
        $location_search = new \UpsProVendor\Ups\Entity\LocationSearchCriteria();
        $location_search->setAccessPointSearch($access_point_search);
        $location_search->setMaximumListSize($maximum_list_size);
        $unit_of_measurement = new \UpsProVendor\Ups\Entity\UnitOfMeasurement();
        $unit_of_measurement->setCode(\UpsProVendor\Ups\Entity\UnitOfMeasurement::UOM_KM);
        $locator_request->setUnitOfMeasurement($unit_of_measurement);
        $locator_request->setLocationSearchCriteria($location_search);
        $locator = new \UpsProVendor\Ups\Locator($this->access_key, $this->user_id, $this->password);
        $locator->setLogger($this->logger);
        return $locator->getLocations($locator_request, \UpsProVendor\Ups\Locator::OPTION_UPS_ACCESS_POINT_LOCATIONS);
    }
    /**
     * Convert location to collection point.
     *
     * @param \stdClass $location structure returned by UPS API. @see docs/location.MD.
     *
     * @return CollectionPoint
     */
    private function convert_location_to_collection_point(\stdClass $location)
    {
        $collection_point = new \UpsProVendor\WPDesk\AbstractShipping\CollectionPoints\CollectionPoint();
        $collection_point->collection_point_id = $location->AccessPointInformation->PublicAccessPointID;
        // phpcs:ignore
        $collection_point->collection_point_name = $location->AddressKeyFormat->ConsigneeName;
        // phpcs:ignore
        $address = new \UpsProVendor\WPDesk\AbstractShipping\Shipment\Address();
        $address->address_line1 = $location->AddressKeyFormat->AddressLine;
        // phpcs:ignore
        $address->postal_code = $location->AddressKeyFormat->PostcodePrimaryLow;
        // phpcs:ignore
        $address->city = $location->AddressKeyFormat->PoliticalDivision2;
        // phpcs:ignore
        $address->country_code = $location->AddressKeyFormat->CountryCode;
        // phpcs:ignore
        $collection_point->collection_point_address = $address;
        return $collection_point;
    }
    /**
     * Get get collection point by given id.
     *
     * @param string $collection_point_id .
     * @param string $country_code .
     *
     * @return CollectionPoint
     * @throws CollectionPointNotFoundException .
     */
    public function get_point_by_id($collection_point_id, $country_code)
    {
        $access_point_id = $collection_point_id;
        $address = new \UpsProVendor\Ups\Entity\AddressKeyFormat();
        $address->setCountryCode($country_code);
        $access_point_search = new \UpsProVendor\Ups\Entity\AccessPointSearch();
        $access_point_search->setAccessPointStatus(\UpsProVendor\Ups\Entity\AccessPointSearch::STATUS_ACTIVE_AVAILABLE);
        $access_point_search->setPublicAccessPointId($access_point_id);
        try {
            $locations = $this->search_access_points($address, $access_point_search, 1);
            return $this->convert_location_to_collection_point($locations->SearchResults->DropLocation);
            // phpcs:ignore
        } catch (\Exception $e) {
            throw new \UpsProVendor\WPDesk\AbstractShipping\Exception\CollectionPointNotFoundException($e->getMessage(), $e->getCode());
        }
    }
    /**
     * Get nearest collection points to given address.
     *
     * @param Address $address .
     *
     * @return CollectionPoint[]
     * @throws CollectionPointNotFoundException .
     */
    public function get_nearest_collection_points(\UpsProVendor\WPDesk\AbstractShipping\Shipment\Address $address)
    {
        $address_key_format = new \UpsProVendor\Ups\Entity\AddressKeyFormat();
        $address_key_format->setAddressLine1($address->address_line1);
        $address_key_format->setAddressLine2($address->address_line2);
        $address_key_format->setCountryCode($address->country_code);
        $address_key_format->setPoliticalDivision2($address->city);
        $address_key_format->setPostcodePrimaryLow($address->postal_code);
        $access_point_search = new \UpsProVendor\Ups\Entity\AccessPointSearch();
        $access_point_search->setAccessPointStatus(\UpsProVendor\Ups\Entity\AccessPointSearch::STATUS_ACTIVE_AVAILABLE);
        try {
            $locations = $this->search_access_points($address_key_format, $access_point_search, 50);
            $collection_points = array();
            if (!\is_array($locations->SearchResults->DropLocation)) {
                // phpcs:ignore
                $locations->SearchResults->DropLocation = array($locations->SearchResults->DropLocation);
                // phpcs:ignore
            }
            foreach ($locations->SearchResults->DropLocation as $location) {
                // phpcs:ignore
                $collection_point = $this->convert_location_to_collection_point($location);
                $collection_points[$collection_point->collection_point_id] = $collection_point;
            }
            return $collection_points;
        } catch (\Exception $e) {
            throw new \UpsProVendor\WPDesk\AbstractShipping\Exception\CollectionPointNotFoundException($e->getMessage(), $e->getCode());
        }
    }
    /**
     * Get single nearest collection point to given address.
     *
     * @param Address $address .
     *
     * @return CollectionPoint
     * @throws CollectionPointNotFoundException .
     */
    public function get_single_nearest_collection_point(\UpsProVendor\WPDesk\AbstractShipping\Shipment\Address $address)
    {
        $collection_points = $this->get_nearest_collection_points($address);
        return \array_shift($collection_points);
    }
}
