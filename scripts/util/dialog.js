
import { jQuery as $ } from '../src/jquery-3.5.1.js';
import '../src/jQuery-UI/jq-ui-core.js';
import '../src/jQuery-UI/effects/jq-ui-effects.js';
import '../src/jQuery-UI/modules/jq-ui-dialog.js';


export default {

    show: function(){
        

                var dialogShown = sessionStorage.getItem("dialog-shown");

                //params.is_user_logged_in = false;dialogShown = false;// <== Debug
                console.log("enabled: " + params.promo_popup_options.enabled[0]);
                console.log("is_user_logged_in: " + params.is_user_logged_in);
                
                console.log("show_dialog: " + params.show_dialog);
                console.log("dialogShown: " + dialogShown);
                if(params.show_dialog && !dialogShown){

                    setTimeout(function(){
                        $( "#dialog-wrapper" ).dialog({
                            autoOpen: true,
                            modal: true,
                            draggable: false,
                            position: { my: "center", at: "center", of: window },
                            resizable: false,
                            classes: {"ui-dialog": "dialog-create-account"},
                            minWidth: 650, 
                            create: function (event, ui) {
                                $(event.target).parent().css('position', 'fixed');
                            },
                            open: function(event, ui) {   //added here
                                jQuery('.ui-widget-overlay').on('click', function() {
                                    $( "#dialog-wrapper" ).dialog('close');
                                });
                            },
                        });

                        sessionStorage.setItem("dialog-shown", true);
                    }, params.promo_popup_options.time_delay_msecs);
                }



    }



}