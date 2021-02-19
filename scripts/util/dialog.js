
import { jQuery as $ } from '../src/jquery-3.5.1.js';
import '../src/jQuery-UI/jq-ui-core.js';
import '../src/jQuery-UI/effects/jq-ui-effects.js';
import '../src/jQuery-UI/modules/jq-ui-dialog.js';


export default {

    show: function(dialogName){
        switch(dialogName){
            case 'create-account':

                var dialogShown = sessionStorage.getItem("dialog-create-account-shown");

                //params.is_user_logged_in = false;dialogShown = false;// <== Debug

                if(!params.is_user_logged_in && !dialogShown){

                    setTimeout(function(){
                        $( "#dialog-create-account" ).dialog({
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
                                    $( "#dialog-create-account" ).dialog('close');
                                });
                            },
                        });

                        sessionStorage.setItem("dialog-create-account-shown", true);
                    }, 4000);
                }
            break;
            

        }


    }



}