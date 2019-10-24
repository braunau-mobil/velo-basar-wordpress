function bm_reset_confirm( clickedid ) { 
    if( document.getElementById( clickedid ).checked == false ) {
        return false;
    } else {
        var box = confirm( "Sicher? Es werden alle Daten des Velobasar Wordpress Plugin gelöscht!" );
        if( box == true )
            return true;
        else
            document.getElementById(clickedid).checked = false;
    }
}
