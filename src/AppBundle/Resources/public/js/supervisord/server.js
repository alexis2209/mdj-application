(function ($) {
    "use strict";
    var SupervisordServer = {

        /**
         * Supervisord server name
         */
        serverName : null,

        /**
         * Worker log container
         */
        workerContainer : null,

        /**
         * Worker messages container
         */
        workerMessagesContainer : null,

        /**
         * Array containing worker log interval to server
         */
        workerLogInterval : [],

        /**
         * Initialization of worker page
         */
        initFunction: function () {

            // Set server name
            this.serverName = $('.server-name').html();

            // Set worker container
            this.workerContainer = $('#worker-container');

            // Set worker messages container
            this.workerMessagesContainer = $('.panel-worker-messages');

            // Initialize worker action buttons
            this.initWorkerActionBtn();

            // Initialize event on log worker buttons
            this.initWorkerLogBtn();

            // Initialize worker messages buttons
            this.initWorkerMessagesBtn();

            // Initialize server chart
            this.initChartServerUsage();
        },

        /**
         * Initialize worker actions buttons
         */
        initWorkerActionBtn: function()
        {
            // Start worker group
            $(document).on('click', '.panel-worker-list .btn-start-group', function() {

                var workerGroup = $(this).closest('tr').data("workergroup");

                // Freeze all actions
                $('tr[data-workergroup='+workerGroup+']').each(function(){
                    SupervisordServer.freezeWorkerAction($(this).data("workername"));
                });

                $.ajax({
                    'type': 'POST',
                    'url': Routing.generate('app_monitoring_supervisord_start_group', {
                        processgroup: workerGroup
                    })
                }).done(function (data) {
                    if (data.code == 'OK') {
                        location.reload();
                    } else {
                        SupervisordServer.addWorkerMessage('warning', data.data);
                    }

                    // Unfreeze all actions
                    $('tr[data-workergroup='+workerGroup+']').each(function(){
                        SupervisordServer.unfreezeWorkerAction($(this).data("workername"));
                    });
                });
            });

            // Start worker process
            $(document).on('click', '.panel-worker-list .btn-start-process', function() {

                var workerName = $(this).closest('tr').data("workername");

                // Freeze all actions
                SupervisordServer.freezeWorkerAction(workerName);

                $.ajax({
                    'type': 'POST',
                    'url': Routing.generate('app_monitoring_supervisord_start_process', {
                        processname: workerName
                    })
                }).done(function (data) {
                    if (data.code == 'OK') {
                        location.reload();
                    } else {
                        SupervisordServer.addWorkerMessage('warning', data.data);
                    }

                    // Unfreeze all actions
                    SupervisordServer.unfreezeWorkerAction(workerName);
                });
            });

            // Stop worker group
            $(document).on('click', '.panel-worker-list .btn-stop-group', function(){

                var workerGroup = $(this).closest('tr').data("workergroup");

                // Freeze all actions
                $('tr[data-workergroup='+workerGroup+']').each(function(){
                    SupervisordServer.freezeWorkerAction($(this).data("workername"));
                });

                $.ajax({
                    'type'  : 'POST',
                    'url'   : Routing.generate('app_monitoring_supervisord_stop_group', {processgroup: workerGroup})
                }).done(function(data){
                    if(data.code == 'OK') {
                        location.reload();
                    } else {
                        SupervisordServer.addWorkerMessage('warning', data.data);
                    }

                    // Unfreeze all actions
                    $('tr[data-workergroup='+workerGroup+']').each(function(){
                        SupervisordServer.unfreezeWorkerAction($(this).data("workername"));
                    });
                });
            });

            // Stop worker process
            $(document).on('click', '.panel-worker-list .btn-stop-process', function(){

                var workerName = $(this).closest('tr').data("workername");

                // Freeze all actions
                SupervisordServer.freezeWorkerAction(workerName);

                $.ajax({
                    'type'  : 'POST',
                    'url'   : Routing.generate('app_monitoring_supervisord_stop_process', {processname: workerName})
                }).done(function(data){
                    if(data.code == 'OK') {
                        location.reload();
                    } else {
                        SupervisordServer.addWorkerMessage('warning', data.data);
                    }

                    // Unfreeze all actions
                    SupervisordServer.unfreezeWorkerAction(workerName);
                });
            });
        },

        /**
         * Initialize worker messages buttons
         */
        initWorkerMessagesBtn: function()
        {
            // Clear worker messages
            $(document).on('click', '.panel-worker-messages .btn-clear', function() {
                $('.panel-body alert', SupervisordServer.workerMessagesContainer).remove();
                SupervisordServer.workerMessagesContainer.hide();
            });
        },

        /**
         * Add worker messages
         * @param type
         * @param message
         */
        addWorkerMessage: function(type, message)
        {
            var $workerMessage = $("<div></div>").addClass('alert alert-'+type).html(message);

            $('.panel-body', this.workerMessagesContainer).append($workerMessage);

            this.workerMessagesContainer.show();
        },

        /**
         * Freeze all worker actions
         * @param workerName
         */
        freezeWorkerAction: function(workerName)
        {
            var $workerLineContainer = $('.panel-worker-list tr[data-workername='+workerName+']');

            $(".worker-actions", $workerLineContainer).hide();
            $(".worker-actions-freeze", $workerLineContainer).show();
        },

        /**
         * Unfreeze all worker actions
         * @param workerName
         */
        unfreezeWorkerAction: function(workerName)
        {
            var $workerLineContainer = $('.panel-worker-list tr[data-workername='+workerName+']');

            $(".worker-actions", $workerLineContainer).show();
            $(".worker-actions-freeze", $workerLineContainer).hide();
        },

        /**
         * Initialize worker log button
         */
        initWorkerLogBtn: function()
        {
            // Start worker log
            $(document).on('click', '.btn-workerlog', function(){
                // Check log screen already created
                var workerName          = $(this).closest('tr').data("workername");
                var workerGroup         = $(this).closest('tr').data("workergroup");
                var $workerLogScreen    = SupervisordServer.findWorkerLogContainer(workerName);

                if(!$workerLogScreen.length) {
                    SupervisordServer.createWorkerLogContainer(workerName);
                    $workerLogScreen    = SupervisordServer.findWorkerLogContainer(workerName);

                    if($workerLogScreen.length != 1) {
                        throw "Error when creating worker log container '"+workerName+"'";
                    }
                }

                SupervisordServer.startWorkerLog(workerGroup, workerName);

                $(this).css('display','none');
            });

            // Close worker log
            $(document).on('click', '#worker-container .btn-close', function(){

                var workerName          = $(this).closest('.panel-worker-log').data("workername");
                var $workerLogScreen    = SupervisordServer.findWorkerLogContainer(workerName);

                if($workerLogScreen.length == 0) {
                    throw "Error when closing worker log container '"+workerName+"' not found";
                }

                SupervisordServer.stopWorkerLog(workerName);
                $workerLogScreen.remove();
                $('tr[data-workername='+workerName+'] .btn-workerlog').css('display', 'inline');
            });

            // Pause worker log
            $(document).on('click', '#worker-container .btn-pause', function(){

                var workerName          = $(this).closest('.panel-worker-log').data("workername");
                var workerGroup = $(".panel-worker-list tr[data-workername="+workerName+"]").data("workergroup");

                // Restart worker log
                if($(this).hasClass('btn-warning')) {
                    SupervisordServer.startWorkerLog(workerGroup, workerName);
                    $(this).removeClass('btn-warning');
                } else {
                    SupervisordServer.stopWorkerLog(workerName);
                    $(this).addClass('btn-warning');
                }
            });
        },

        /**
         * Create worker log container
         * @param workerName
         */
        createWorkerLogContainer: function(workerName)
        {
            var $workerContainer            = $("<div></div>").addClass("panel panel-default panel-worker-log").attr("data-workername", workerName);
            var $workerContainerHeader      = $("<div></div>").addClass("panel-heading").html("Log worker '"+workerName+"'");
            var $workerContainerHeaderTools = $("<div></div>").addClass('pull-right').html("<i class='fa fa-fw fa-pause btn-pause'></i><i class='fa fa-fw fa-times btn-close'></i>");
            var $workerContainerBody        = $("<div></div>").addClass("panel-body");

            $workerContainerHeader.append($workerContainerHeaderTools);
            $workerContainer.append($workerContainerHeader).append($workerContainerBody);
            this.workerContainer.append($workerContainer);
        },

        /**
         * Find a worker container in DOM
         * @param workerName
         * @returns {*|HTMLElement}
         */
        findWorkerLogContainer: function(workerName)
        {
            return $('.panel-worker-log[data-workername='+workerName+']');
        },

        /**
         * Start requesting server for worker log
         * @param workerGoup
         * @param workerName
         */
        startWorkerLog: function(workerGoup, workerName)
        {
            if (workerName in this.workerLogInterval) {
                throw "Cannot redeclare interval for worker '"+workerName+"'";
            }

            this.workerLogInterval[workerName] = setInterval(function() { SupervisordServer.updateWorkerLog(workerGoup,workerName); }, 3000);
        },

        /**
         * Update worker log
         * @param workerGroup
         * @param workerName
         */
        updateWorkerLog: function(workerGroup, workerName)
        {
            $.get(Routing.generate('app_monitoring_supervisord_log_stdout', {workergroup : workerGroup, workername : workerName}))
            .done(function(data){
                    var $workerLogScreen = SupervisordServer.findWorkerLogContainer(workerName);
                    $('.panel-body', $workerLogScreen).empty().html(data);
            });
        },

        /**
         * Stop requesting server for worker log
         * @param workerName
         * @returns {boolean}
         */
        stopWorkerLog: function(workerName)
        {
            if (workerName in this.workerLogInterval) {
                clearInterval(this.workerLogInterval[workerName]);
                delete this.workerLogInterval[workerName];
                return true;
            }

            throw "Worker interval '"+workerName+"' not found";
        },

        /**
         * Initialize server usage chart
         */
        initChartServerUsage: function()
        {
            this.workerStatsChart = Morris.Line({
                element: 'chart-server-usage',
                data: [],
                xkey: 'y',
                ykeys: ['a','b'],
                labels: ['Memory','CPU']
            });

            this.updateChartServerUsage();

            setInterval(function() {
                SupervisordServer.updateChartServerUsage();
            }, 10000);
        },

        /**
         * Update the server usage charts
         */
        updateChartServerUsage: function()
        {
            $.ajax({
                'type'  : 'GET',
                'url'   : Routing.generate('app_monitoring_supervisord_server_usage', {})
            }).done(function(data){
                SupervisordServer.workerStatsChart.setData(data);
            });
        }
    };

    /// Initializing ///
    $(document).ready(function () {
        SupervisordServer.initFunction();
    });
}(jQuery));
