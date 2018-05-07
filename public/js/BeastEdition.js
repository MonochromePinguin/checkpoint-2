$(document).ready( function(){
    $planetSelector = $('#planet');
    $newPlanetOption = $('#option-create-new');

    $planetSelector.append( new Option('Nouvelle planète ...', 'new-planet') );

    $planetSelector.change( function() {

        //was the "nouvelle planète ..." option selected ?
        if ( $newPlanetOption.selected ) {
console.log('sélected');
        }
    });

})
