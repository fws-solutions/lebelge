jQuery( function( $ ) {
	"use strict";
	var wcap_atc_modal_data = {
	    wcap_heading_section_text_email: wcap_vue_field_data_params.wcap_atc_head,
	    wcap_text_section_text_field:    wcap_vue_field_data_params.wcap_atc_text,
	    wcap_email_placeholder_section_input_text: wcap_vue_field_data_params.wcap_atc_email_place,
	    wcap_button_section_input_text : wcap_vue_field_data_params.wcap_atc_button,
	    wcap_button_bg_color : wcap_vue_field_data_params.wcap_atc_button_bg_color,
	    wcap_button_text_color : wcap_vue_field_data_params.wcap_atc_button_text_color,
	    wcap_popup_text_color : wcap_vue_field_data_params.wcap_atc_popup_text_color,
	    wcap_popup_heading_color : wcap_vue_field_data_params.wcap_atc_popup_heading_color,
	    wcap_non_mandatory_modal_input_text : wcap_vue_field_data_params.wcap_atc_non_mandatory_input_text,
		wcap_phone_placeholder_section_input_text : wcap_vue_field_data_params.wcap_phone_placeholder_section_input_text,
		wcap_atc_button: {
			backgroundColor: wcap_vue_field_data_params.wcap_atc_button_bg_color,
			color          : wcap_vue_field_data_params.wcap_atc_button_text_color  
		},
		wcap_atc_popup_text:{
			color          : wcap_vue_field_data_params.wcap_atc_popup_text_color,	
		},
		wcap_atc_popup_heading:{
			color          : wcap_vue_field_data_params.wcap_atc_popup_heading_color,	
		}
	};

	var myViewModel = new Vue({
	    el: '#wcap_popup_main_div',
	    data: wcap_atc_modal_data,
	    watch: {
            'wcap_button_bg_color': function( val, oldVal ){
            	wcap_atc_modal_data.wcap_atc_button.backgroundColor = val;
            },

            'wcap_button_text_color': function( val, oldVal ){
            	wcap_atc_modal_data.wcap_atc_button.color = val;
            },
            'wcap_popup_text_color': function( val, oldVal ){
            	wcap_atc_modal_data.wcap_atc_popup_text.color = val;
            },
            'wcap_popup_heading_color': function( val, oldVal ){
            	wcap_atc_modal_data.wcap_atc_popup_heading.color = val;
            },
            deep: true,
		}
	});
});