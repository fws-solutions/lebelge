<?php

/**
 * Class of shipping method launched by WooCommerce
 *
 * @package WPDesk\WooCommerceShipping
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping;

use UpsProVendor\WPDesk\AbstractShipping\ShippingService;
use UpsProVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRate;
use UpsProVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRateToCollectionPoint;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\HasCollectionPointFlatRate;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\CollectionPoint\CollectionPointRateMethod;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Fallback\FallbackRateMethod;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\FlatRateRateMethod\CollectionPointFlatRateRateMethod;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Standard\StandardServiceRateMethod;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\DeliveryDatesTrait;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\MetaDataTrait;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\RateWithRateMethodsTrait;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\LoggerTrait;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\SettingsTrait;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\Traits\ShippingServiceTrait;
/**
 * Class ShippingMethod
 */
class ShippingMethod extends \WC_Shipping_Method
{
    use LoggerTrait;
    use RateWithRateMethodsTrait;
    use ShippingServiceTrait;
    use SettingsTrait;
    use MetaDataTrait;
    use DeliveryDatesTrait;
    /** @var PluginShippingDecisions */
    protected static $plugin_shipping_decisions;
    /**
     * Is method enabled.
     *
     * @var bool
     */
    private $is_method_enabled = \false;
    /**
     * ShipmentMethod constructor.
     *
     * @param int $instance_id Instance ID.
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);
        $shipping_service = $this->get_shipping_service($this);
        $this->id = $shipping_service->get_unique_id();
        $this->title = $shipping_service->get_name();
        $this->method_title = $shipping_service->get_name();
        $this->method_description = $shipping_service->get_description();
        $this->form_fields = $this->get_form_fields_from_shipping_service($this->get_plugin_shipping_decisions());
        $this->tax_status = 'taxable';
        $this->init();
        if ($this->instance_id) {
            $this->title = $this->get_option('title', $this->title);
        }
    }
    /**
     * Set shipping service.
     *
     * @param PluginShippingDecisions $plugin_shipping_decisions .
     */
    public static function set_plugin_shipping_decisions(\UpsProVendor\WPDesk\WooCommerceShipping\PluginShippingDecisions $plugin_shipping_decisions)
    {
        self::$plugin_shipping_decisions = $plugin_shipping_decisions;
    }
    /**
     * @return PluginShippingDecisions .
     */
    public function get_plugin_shipping_decisions()
    {
        return static::$plugin_shipping_decisions;
    }
    /**
     * Init method.
     */
    protected function init()
    {
        $this->metadata_builder = $this->create_metadata_builder();
        $this->build_form_fields();
        $this->init_settings();
        if (empty($this->instance_settings)) {
            $this->init_instance_settings();
        }
        $this->enable_shipping_method_if_not_exists($this->settings);
        $this->is_method_enabled = 'yes' === $this->get_option('enable_shipping_method', 'yes');
        \add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }
    /**
     * Create meta data builder.
     *
     * @return WooCommerceShippingMetaDataBuilder
     */
    protected function create_metadata_builder()
    {
        return new \UpsProVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder($this);
    }
    /**
     * Init form fields.
     */
    public function build_form_fields()
    {
        $this->form_fields = $this->add_rate_methods_settings($this->form_fields);
    }
    /**
     * Enable shipping method by default.
     *
     * @param array $settings Settings.
     */
    private function enable_shipping_method_if_not_exists($settings)
    {
        if (empty($settings['enable_shipping_method'])) {
            $this->settings['enable_shipping_method'] = 'yes';
        }
    }
    /**
     * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
     *
     * @param array $package Package array.
     */
    public function calculate_shipping($package = [])
    {
        if ($this->is_method_enabled) {
            if ($this instanceof \UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\HasCollectionPointFlatRate && $this->is_flat_rate_enabled($this)) {
                $this->add_rate_method(new \UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\FlatRateRateMethod\CollectionPointFlatRateRateMethod($this->get_flat_rate_cost($this), $this->get_flat_rate_shipping_rate_suffix($this)));
            } else {
                $service = $this->get_shipping_service($this);
                $sender_address = $this->create_sender_address();
                if ($service instanceof \UpsProVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRate) {
                    $this->add_rate_method(new \UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Standard\StandardServiceRateMethod($service));
                }
                if ($service instanceof \UpsProVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRateToCollectionPoint) {
                    $this->add_rate_method(new \UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\CollectionPoint\CollectionPointRateMethod($service));
                }
                $this->add_rate_method(new \UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Fallback\FallbackRateMethod('yes' === $this->get_option('debug_mode', 'no')));
            }
            $logger = $this->inject_logger_into($this->get_shipping_service($this));
            $this->handle_rating_using_methods($logger, $this->get_shipping_service($this), $package, $this->metadata_builder);
        }
    }
}
