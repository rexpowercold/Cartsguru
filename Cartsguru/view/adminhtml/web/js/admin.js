/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2017
 * @license   Commercial license
 */
 require([
     'jquery',
     'domReady!'
],function ($, domReady) {
    // On ready
    // $(document).ready(function ($) {
        // Switch active view
        function switchView(view, backToView) {
            if (!view){
                view =  window.cg_backto;
                 window.cg_backto = null;
            }

            $('#cartsguru-welcome').prop('class', '');
            switch(view){
                case 'view-try-it':
                case 'view-have-account':
                case 'view-success':
                case 'view-no-store-selected':
            }
            $('#cartsguru-welcome').addClass(view);

            window.cg_backto = backToView;
        }

        //Declare global functions
        window.cg_switchView = switchView;
});
