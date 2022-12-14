<?php
defined( 'ABSPATH' ) || exit;

ob_start();
?>
    <div style="display:none;" class="xlwcty_tb_content" id="xlwcty_component_settings<?php echo $config['slug']; ?>_help">
        <h3><?php echo $config['title'] . ' ' . __( 'Component Design & Settings', 'thank-you-page-for-woocommerce-nextmove' ); ?></h3>
        <p class="xlwcty_center"><img src="//storage.googleapis.com/xl-nextmove/social-share.jpg"/></p>
        <table align="center" width="650" class="xlwcty_modal_table">
            <tr>
                <td width="50">1.</td>
                <td><strong>Heading:</strong> Enter any heading. Customize font size and text alignment too.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td><strong>Description:</strong> Enter any text here. Alignment option available here.</td>
            </tr>
            <tr>
                <td>3.</td>
                <td><strong>Enable Facebook Share:</strong> If you wish to display Facebook Share feature, enable it. After enable enter your message that appear while sharing from thank you page.
                </td>
            </tr>
            <tr>
                <td>4.</td>
                <td><strong>Enable Twitter Share:</strong> If you wish to display Twitter Share feature, enable it. After enable enter your message that appear while sharing from thank you page.</td>
            </tr>
            <tr>
                <td>5.</td>
                <td><strong>Button:</strong> Here you can enter the button text and css properties like font size, text color or background color.</td>
            </tr>
            <tr>
                <td>6.</td>
                <td><strong>Border:</strong> You can add any border style, manage width or color. Or if you want to disable the border, choose border style option 'none'.</td>
            </tr>
        </table>
    </div>
<?php
return ob_get_clean();
