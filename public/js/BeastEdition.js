/* these JS functions deal with the "create new planet" form  inside
the "new beast" page and add some interactivity */

$(document).ready( function(){

    $planetSelector = $('#planet');
    $dialog = $('#planet-creation-dialog');
    $planetName = $('#new-planet-name');
    $sendBtn = $('#new-planet-send-btn');

    $spinner = $('#spinner');
    $ajaxMessage = $('#ajax-message');


    function inhibitSendBtn() {
        $sendBtn.attr('disabled', '');
        $sendBtn.prop('title', 'Il faut d\'abord rentrer un nom de planète');
    }


    //hide the dialog, clear the input field, and other cleanup
    // when the planet creation is cancel
    $('#new-planet-cancel-btn').click( function() {
        $planetSelector.val( $planetSelector.children('option').first().val() );

        $dialog.modal('hide');
        $planetName.val('');

        $spinner.css('display', 'none');
        $ajaxMessage.text('');

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
//TODO: show it each time the option is clicked ...
    $planetSelector.change( function() {
        //was the "nouvelle planète ..." option selected?
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

        $spinner.css('display', 'inline-block');
        $ajaxMessage.text('attente d\'une réponse du serveur ...');

        $.post(
            '/planets/AJAXadd',
            { planetToCreate: $planetName.val() },
            handleValidAjaxResponse,
            'json'

        // fail() allows to handle a failure of the request ...
        ).fail( function() {
            $ajaxMessage.text(
                'Impossible de créer la planète pour l\'instant&nbsp;:' +
                ' erreur de communication avec le serveur'
            );

        // we alwais need to hide the spinner after usage
        }).always( function() {
            $spinner.css('display', 'none');
        });
    });


    /***
    * get back the server response for planet creation
    *@param object data    awaited fields:
    *                       see src/Controller/PlanetController.php::ajaxAddNew()
    *                       (status, id, newPlanetList, message)
    */
    function handleValidAjaxResponse(data) {
        switch (data.status) {
            //planet creation ok, we've got a planet id and a planet list back
            case 201:
                //replace the planet selector options with the new ones
                $planetSelector.html(
                    generateOptionList(data.id, data.newPlanetList)
                );
                $dialog.modal('hide');
                $ajaxMessage.text('');
                $planetName.val('');
                break;

            //planet already exist
            case 304:
                $planetSelector.val(
                    $planetSelector.children(
                        'option[value="' + data.id + '"]'
                    ).val()
                );
                $dialog.modal('hide');
                $ajaxMessage.text('');
                $planetName.val('');
                break;

            default:
                $ajaxMessage.text(
                    'code de retour «' + data.status +'» renvoyé par le serveur :' +
                    "\n" + data.message
                );
        }
    }

    /***
     * generate the html code for a list of options to stuff into
     *      a <select> element. Each option will have its value and text content
     *      given by an element of the array list
     * @param selectedValue     the value of the option to mark as "selected"
     * @param optionList        a list of arrays [ 'id', 'name' ]
     * @returns {string}
     */
    function generateOptionList(selectedValue, optionList) {
        var result = '';

        for(let option of optionList ) {
            selected = (selectedValue == option.id) ? 'selected' : '';
            result += `<option value="${option.id}" ${selected}>${option.name}</option>\n`;
        }

        return result;
    }
})
