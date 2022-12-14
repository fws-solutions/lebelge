<?php

if (!defined('ABSPATH')) {
    exit;
}

$tax_display = $order->get_cart_tax();

$total_rows = array();

if ($subtotal = $order->get_subtotal_to_display(false, $tax_display)) {
    $total_rows['cart_subtotal'] = array(
        'label' => __('Subtotal:', 'woocommerce'),
        'value' => $subtotal
    );
}

if ($order->get_total_discount() > 0) {
    $total_rows['discount'] = array(
        'label' => __('Discount:', 'woocommerce'),
        'value' => '-' . $order->get_discount_to_display($tax_display)
    );
}

if ($order->get_shipping_tax()) {
    $total_rows['shipping'] = array(
        'label' => 'Shipping Tax',
        'value' => $order->get_shipping_tax()
    );
}

if ($order->get_total_shipping()) {
    $total_rows['shipping'] = array(
        'label' => 'Shipping Total',
        'value' => $order->get_total_shipping()
    );
}

if ($fees = $order->get_fees()) {
    foreach ($fees as $id => $fee) {
        if (apply_filters('woocommerce_get_order_item_totals_excl_free_fees', $fee['line_total'] + $fee['line_tax'] == 0, $id)) {
            continue;
        }

        if ('excl' == $tax_display) {
            $total_rows['fee_' . $id] = array(
                'label' => ($fee['name'] ? $fee['name'] : __('Fee', 'woocommerce')) . ':',
                'value' => wc_price($fee['line_total'], array('currency' => $order->get_currency()))
            );
        } else {
            $total_rows['fee_' . $id] = array(
                'label' => $fee['name'] . ':',
                'value' => wc_price($fee['line_total'] + $fee['line_tax'], array('currency' => $order->get_currency()))
            );
        }
    }
}

if ('excl' === $tax_display) {
    if (get_option('woocommerce_tax_total_display') == 'itemized') {
        foreach ($order->get_tax_totals() as $code => $tax) {
            $total_rows[sanitize_title($code)] = array(
                'label' => $tax->label . ':',
                'value' => $tax->formatted_amount
            );
        }
    } else {
        $total_rows['tax'] = array(
            'label' => WC()->countries->tax_or_vat() . ':',
            'value' => wc_price($order->get_total_tax(), array('currency' => $order->get_currency()))
        );
    }
}

if ($refunds = $order->get_refunds()) {
    foreach ($refunds as $id => $refund) {
        $total_rows['refund_' . $id] = array(
            'label' => $refund->get_reason() ? $refund->get_reason() : __('Refund', 'woocommerce') . ':',
            'value' => wc_price('-' . $refund->get_amount(), array('currency' => $order->get_currency()))
        );
    }
}

$total_rows['order_total'] = array(
    'label' => __('Total:', 'woocommerce'),
    'value' => $order->get_formatted_order_total($tax_display)
);

?>

<table>
    <?php
    foreach ($total_rows as $index => $value) {
        ?>

        <tr>
            <td <?php
            if ($value['label'] == 'Total:') {
            ?>
                    style="font-weight: bold;">
                <?php echo $value['label'];
                $value['value'] = '<span style="border-top: 1px solid #000;font-weight: bold;padding-top: 10px;">' . $value['value'] . '</span>';
                } else {
                    echo '><span style="font-weight: bold;"><span/>' . $value['label'];
                } ?></td>
            <td> <?php echo $value['value'] ?></td>
        </tr>

        <?php
    }
    ?>
</table>
