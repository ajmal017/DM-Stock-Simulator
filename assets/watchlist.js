jQuery(function($){
    $('#stockswatchlist .nav-pills a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })

    $('.sparklines').sparkline('html', { enableTagOptions: true });
    var $search_ticker = $( "#ticker_search" );

    if($search_ticker.length)
    $search_ticker.autocomplete({
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
            $('#ticker_search').val(ui.item.value);
            $('#ticker_search_data').html( 'Fetching Data for <strong>' + ui.item.value + '</strong>' );

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

                    $('#ticker_search_data').html(ticker_data);

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



    $('.stock-table table tr').each(function(){
        var $symbol = $(this).data('symbol');
        var $thisRow = $(this);
        if($symbol && $symbol.length){
            $.ajax( {
                url: "/wp-admin/admin-ajax.php?action=get_stock_data",
                type: 'POST',
                data: {
                    s:$symbol
                },
                success: function( data ) {
                    console.log(data);


                    var statistics = JSON.parse(data.statistics)

                    var $thisRowCells = [];

                    var $latestHistory = data.history[data.history.length - 1] || [];


                    $thisRowCells.push('<td class="value-subdetails"> <label class="lead-text">'+data.symbol+'</label> <small class="help-text">'+data.name+'</small> </td>');
                    $thisRowCells.push('<td class="sparkwrap"><span class="sparklines" data-symbol="'+data.symbol+'"></span></td>');
                    $thisRowCells.push('<td> <button class="btn btn-sm btn-success"><i class="fa fa-dollar"></i> '+parseFloat(statistics.price).toFixed(2)+'</button> </td>');
                    $thisRowCells.push('<td> <button class="btn btn-sm btn-danger"><i class="fa fa-dollar"></i> '+parseFloat(statistics.price).toFixed(2)+'</button> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat(statistics.change).toFixed(2)+'</label> <small class="help-text">'+parseFloat(statistics.change_percent).toFixed(2)+'%</small> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat($latestHistory.high).toFixed(2)+'</label> <small class="help-text">&nbsp;</small> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat($latestHistory.low).toFixed(2)+'</label> <small class="help-text">&nbsp;</small> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat($latestHistory.open).toFixed(2)+'</label> <small class="help-text">&nbsp;</small> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat($latestHistory.close).toFixed(2)+'</label> <small class="help-text">&nbsp;</small> </td>');
                    $thisRowCells.push('<td class="value-subdetails subdetails-right"> <label class="lead-text">'+parseFloat($latestHistory.volume).toFixed(0)+'</label> <small class="help-text">&nbsp;</small> </td>');


                    $thisRow.html($thisRowCells.join(''));
                    var $sparkspan = $thisRow.find('[data-symbol="'+data.symbol+'"]').first();

                    var history = { open : [] , close : [] };
                    for(var i in data.history){
                        history.open.push(parseFloat(data.history[i].open));
                        history.close.push(parseFloat(data.history[i].close));
                    }


                    console.log(history);

                    var $color = {
                        'good' : [ '#a4e2a4' , '#4cae4c' ],
                        'bad' : [ '#d78886' , '#ac2925' ],
                    };

                    var $barcolor = '#ddd';
                    var $linecolor = '#000';

                    if(statistics.change > 0){
                        $barcolor = $color.good[0];
                        $linecolor = $color.good[1];
                    }else{
                        $barcolor = $color.bad[0];
                        $linecolor = $color.bad[1];
                    }

                    $sparkspan.sparkline(history.open, {height: '30px', type: 'bar', barSpacing: 0, barWidth: 3, barColor: $barcolor, tooltipPrefix: 'Open: '});
                    $sparkspan.sparkline(history.close, {composite: true, height: '30px', fillColor:false, lineColor: $linecolor,lineWidth: 2, tooltipPrefix: 'Close: '});
                }
            } );
        }
    });

    $('#add-symbol-to-user').click(function(){
        var $ticker_symbol = $('#ticker_search').val();
        if($ticker_symbol.length){

            $(this).text('Adding Symbol...');
            $(this).addClass('disabled');


            $.ajax( {
                url: "/wp-admin/admin-ajax.php?action=save_stock_data",
                type: 'POST',
                data: {
                    s: $ticker_symbol
                },
                success: function( data ) {
                    console.log(data);

                    $.ajax( {
                        url: "/wp-admin/admin-ajax.php?action=add_stock_data_to_user",
                        type: 'POST',
                        data: {
                            s: $ticker_symbol
                        },
                        success: function( data ) {
                            window.location.reload();
                        },
                        error: function(data) {
                            $(this).text('Add Symbol');
                            $(this).removeClass('disabled');
                            console.log(data);
                        }
                    } );


                }
            } );

        }
    });

});
