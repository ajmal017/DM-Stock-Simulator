jQuery(function($){
    var dialog, form,

    dialog = $( "#dialog-form" ).dialog({
        autoOpen: false,
        height: 700,
        width: 650,
        modal: true,
        buttons: {
            "Add Symbol": function(event, ui){
                var symbol = $('#ticker_symbol').val();
                if( symbol !=''){
                    $(event.currentTarget).html('<span class="ui-button-text">Adding Symbol...</span>');
                    $.ajax( {
                        url: "/wp-admin/admin-ajax.php?action=save_stock_data",
                        type: 'POST',
                        data: {
                            s: symbol
                        },
                        success: function( data ) {
                            if(data=='exist'){
                                alert('Stock Symbol already exist');
                            }else{
                                window.location.reload();
                                dialog.dialog( "close" );
                            }
                            $(event.currentTarget).html('<span class="ui-button-text">Add Symbol</span>');
                        }
                    } );

                }
            },
            Cancel: function() {
                dialog.dialog( "close" );
            }
        },
        open: function( event, ui ) {
            $('#ticker_data').val('');
        },
        close: function() {

        }
    });

    form = dialog.find( "form" ).on( "submit", function( event ) {
        event.preventDefault();
        console.log($('#ticker_data').val());
    });

    $( "button#add-stocks" ).button().on( "click", function() {
        dialog.dialog( "open" );
    });



    $( "#ticker_symbol" ).autocomplete({
        source: function( request, response ) {
            $.ajax( {
                url: "/wp-admin/admin-ajax.php",
                type: 'GET',
                data: {
                    s: request.term,
                    action: 'stock_ticker_search_symbol'
                },
                success: function( data ) {
                    response( data );
                }
            } );
        },
        minLength: 2,
        select: function( event, ui ) {
            $('#ticker_symbol').val(ui.item.value);
            $('#ticker_data').html( 'Fetching Data for <strong>' + ui.item.value + '</strong>' );

            $.ajax( {
                url: "/wp-admin/admin-ajax.php?action=get_stock_data",
                type: 'POST',
                data: {
                    s: ui.item.value
                },
                success: function( data ) {

                    var ticker_data = '';

                    if(data.value!=''){

                        ticker_data = '<strong>Symbol</strong> : ' + data.symbol + '<br>';
                        ticker_data += '<strong>Name</strong> : ' + data.name + '<br><br>';
                        ticker_data += '<div id="ticker_chart" style="height: 400px; width: 100%;"></div>';

                    }

                    $('#ticker_data').html(ticker_data);

                    $('#ticker_chart').html();

                    new TradingView.MediumWidget({
                        "container_id": "ticker_chart",
                        "symbols": [
                            [
                                data.name.replace(/[^0-9a-z\s]/gi, ''),
                                data.symbol
                            ]
                        ],
                        "gridLineColor": "#e9e9ea",
                        "fontColor": "#83888D",
                        "underLineColor": "#dbeffb",
                        "trendLineColor": "#4bafe9",
                        "width": "100%",
                        "height": "100%",
                        "locale": "en"
                    });

                }
            });
        }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<div>" + item.value + "<br>" + item.label + "</div>" )
        .appendTo( ul );
    };;


    var chartDiv = $('#stockChartDataDiv');

    if(chartDiv.length){

        $.ajax( {
            url: "/wp-admin/admin-ajax.php?action=get_stock_data",
            type: 'POST',
            data: {
                s: chartDiv.data('symbol')
            },
            success: function( data ) {
                console.log(data);

                $('.stock-data-symbol').text(data.symbol);
                $('.stock-data-name').text(data.name);
                $('.stock-data-type').text(data.type);

                var profile = JSON.parse(data.profile)


                $('.stock-data-summary').text(profile.longBusinessSummary);

                $('.stock-data-address').text(profile.address1);
                $('.stock-data-city').text(profile.city);
                $('.stock-data-state').text(profile.state);
                $('.stock-data-country').text(profile.country);
                $('.stock-data-zip').text(profile.zip);
                $('.stock-data-phone').text(profile.phone);
                $('.stock-data-website').text(profile.website);
                $('.stock-data-industry').text(profile.industry);
                $('.stock-data-sector').text(profile.sector);
                $('.stock-data-employees').text(profile.fullTimeEmployees);

            }
        } );

        $.ajax( {
            url: "/wp-admin/admin-ajax.php?action=updateStockData",
            type: 'POST',
            data: {
                symbol: chartDiv.data('symbol')
            },
            success: function( data ) {

                $.ajax( {
                    url: "/wp-admin/admin-ajax.php?action=get_stock_history",
                    type: 'POST',
                    data: {
                        s: chartDiv.data('symbol')
                    },
                    success: function( data ) {
                        for (var i = 0; i < data.length; i++) {
                            data[i].date = new Date(data[i].date.toString());

                            data[i].open = parseFloat(data[i].open);
                            data[i].close = parseFloat(data[i].close);
                            data[i].high = parseFloat(data[i].high);
                            data[i].low = parseFloat(data[i].low);
                            data[i].volume = parseFloat(data[i].volume);
                        }

                        getStockHistory(data);
                    }
                } );

            }
        } );





        function addPanel() {
            var chart = AmCharts.charts[ 0 ];
            if ( chart.panels.length == 1 ) {
                var newPanel = new AmCharts.StockPanel();
                newPanel.allowTurningOff = true;
                newPanel.title = "Volume";
                newPanel.showCategoryAxis = false;

                var graph = new AmCharts.StockGraph();
                graph.valueField = "volume";
                graph.fillAlphas = 0.15;
                newPanel.addStockGraph( graph );

                var legend = new AmCharts.StockLegend();
                legend.markerType = "none";
                legend.markerSize = 0;
                newPanel.stockLegend = legend;

                chart.addPanelAt( newPanel, 1 );
                chart.validateNow();
            }
        }

        function removePanel() {
            var chart = AmCharts.charts[ 0 ];
            if ( chart.panels.length > 1 ) {
                chart.removePanel( chart.panels[ 1 ] );
                chart.validateNow();
            }
        }

        function getStockHistory(data){
            var chart = AmCharts.makeChart( "stockChartDataDiv", {
                "type": "stock",
                "theme": "light",
                "dataSets": [ {
                    "fieldMappings": [ {
                        "fromField": "open",
                        "toField": "open"
                    }, {
                        "fromField": "close",
                        "toField": "close"
                    }, {
                        "fromField": "high",
                        "toField": "high"
                    }, {
                        "fromField": "low",
                        "toField": "low"
                    }, {
                        "fromField": "volume",
                        "toField": "volume"
                    }, {
                        "fromField": "value",
                        "toField": "value"
                    } ],
                    "color": "#7f8da9",
                    "dataProvider": data,
                    "categoryField": "date"
                } ],
                "balloon": {
                    "horizontalPadding": 13
                },
                "panels": [ {
                    "title": "Value",
                    "stockGraphs": [ {
                        "id": "g1",
                        "type": "candlestick",
                        "openField": "open",
                        "closeField": "close",
                        "highField": "high",
                        "lowField": "low",
                        "valueField": "close",
                        "lineColor": "#7f8da9",
                        "fillColors": "#7f8da9",
                        "negativeLineColor": "#db4c3c",
                        "negativeFillColors": "#db4c3c",
                        "fillAlphas": 1,
                        "balloonText": "open:<b>[[open]]</b><br>close:<b>[[close]]</b><br>low:<b>[[low]]</b><br>high:<b>[[high]]</b>",
                        "useDataSetColors": false
                    } ]
                } ],
                "scrollBarSettings": {
                    "graphType": "line",
                    "usePeriod": "DD"
                },
                "panelsSettings": {
                    "panEventsEnabled": true
                },
                "cursorSettings": {
                    "valueBalloonsEnabled": true,
                    "valueLineBalloonEnabled": true,
                    "valueLineEnabled": true
                },
                "periodSelector": {
                    "position": "bottom",
                    "periods": [ {
                        "period": "DD",
                        "count": 7,
                        "label": "1 Week"
                    }, {
                        "period": "MM",
                        "count": 1,
                        "selected" : true,
                        "label": "1 Month"
                    }, {
                        "period": "YY",
                        "count": 1,
                        "label": "1 Year"
                    }, {
                        "period": "MAX",
                        "label": "MAX"
                    } ]
                }
            } );
        }
    }

});
