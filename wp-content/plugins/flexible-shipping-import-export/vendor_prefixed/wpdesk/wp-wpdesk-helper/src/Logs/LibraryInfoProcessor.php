<?php

namespace FlexibleShippingImportExportVendor\WPDesk\Helper\Logs;

use FlexibleShippingImportExportVendor\Monolog\Processor\ProcessorInterface;
use FlexibleShippingImportExportVendor\WPDesk\Helper\Debug\LibraryDebug;
/**
 * Can add context with libraries to the logs
 *
 * @package WPDesk\Helper\Logs
 */
class LibraryInfoProcessor implements \FlexibleShippingImportExportVendor\Monolog\Processor\ProcessorInterface
{
    /** @var LibraryDebug */
    private $library_debug;
    public function __construct(\FlexibleShippingImportExportVendor\WPDesk\Helper\Debug\LibraryDebug $library_debug)
    {
        $this->library_debug = $library_debug;
    }
    /*
     * @see https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#using-processors
     */
    public function __invoke(array $record)
    {
        try {
            $files_report = $this->library_debug->get_wpdesk_vendor_files_report();
            $record['extra']['library_report'] = $this->library_debug->get_libraries_report($files_report);
        } catch (\Exception $e) {
            $record['extra']['library_report'] = 'Exception ' . $e->getMessage();
        }
        return $record;
    }
}
