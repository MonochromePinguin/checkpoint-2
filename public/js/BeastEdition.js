$(document).ready( function(){
    $planetSelector = $('#planet');
    $dialog = $('#planet-creation-dialog');
    $planetName = $('#new-planet-name');
    $sendBtn = $('#new-planet-send-btn');


    function inhibitSendBtn() {
        $sendBtn.attr('disabled', '');
        $sendBtn.prop('title', 'Il faut d\'abord rentrer un nom de planète');
    }


    // hide the dialog and clear the input field on cancel
    $('#new-planet-cancel-btn').click( function() {
        $planetSelector.val( $planetSelector.children('option').first().val() );

        $dialog.modal('hide');
        $planetName.val('');
        inhibitSendBtn();
    });


    //(de)inhibit the send button depending on name field content
    $planetName.keyup( function() {
        if ( '' != $planetName.val() ) {
            $sendBtn.removeAttr('disabled');
            $sendBtn.prop('title', 'cliquer pour envoyer la demande de création');
        } else
            inhibitSendBtn();
    });


    //show the planet creation dialog on **change** only ...
//TODO: show it anytime the option is clicked ...
    $planetSelector.change( function() {
        //was the "nouvelle planète ..." option selected ?
        $optId = $('#planet option:selected').attr('id');

        if ( $optId == 'option-create-new' ) {
            //Yes, we can show the planet creation form
            $dialog.modal('show');
            //set focus on the field
            $dialog.on('shown.bs.modal', function () {
                    $planetName.trigger('focus');
            });
        }
    });


    //on click, send a request to add a new planet through AJAX
    $sendBtn.click( function() {
         $.get( '/planets/ajax?createNew ' + $planetName.val(''),
                function(data, status){
            alert("Data: " + data + "\nStatus: " + status);
        });


    })
})
