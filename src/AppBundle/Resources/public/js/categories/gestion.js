(function ($) {
    "use strict";
    var GestionCategories = {

        /**
         * Initialization of worker page
         */
        initFunction: function () {
            this.initUpdate();
        },


        /**
         * Initialize worker actions buttons
         */
        initUpdate: function() {
            $(document).on('click', '.validCateg', function () {
                var testSource = $(this).data('source');
                var selectedCountry = $("#selectwpcat"+testSource+" option:selected").val();
                console.log("test 1 : "+testSource);
                console.log("test 2 : "+selectedCountry);

                $.ajax({
                    'type': 'GET',
                    'url': Routing.generate('admin_categories_validation', {
                        source: testSource,
                        dest: selectedCountry
                    })
                }).done(function (data) {
                    if (data.code == 'OK') {
                        alert('OK')
                    } else {
                        alert(data.message)
                    }

                    // Unfreeze all actions
                    $('tr[data-workergroup='+workerGroup+']').each(function(){
                        SupervisordServer.unfreezeWorkerAction($(this).data("workername"));
                    });
                });
            });
        }
    };

    /// Initializing ///
    $(document).ready(function () {
        GestionCategories.initFunction();
    });
}(jQuery));
