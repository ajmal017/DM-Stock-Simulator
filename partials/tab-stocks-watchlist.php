<div class="row">
    <div id="stockswatchlist" class="col-xs-12">

        <h4>Stocks Watchlist</h4>

            <div class="btn-group" role="group" aria-label="...">
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target=".add-ticker-symbol">Add Ticker Symbol</button>


                <button type="button" class="btn btn-sm btn-white"><i class="fa fa-trash nopadding"></i></button>
            </div>
            <div class="stock-table margin-top-20">
                <table class="table table-bordered table-hovered">
                    <thead>
                        <th>Stock Name</th>
                        <th>Graph</th>
                        <th>Sell</th>
                        <th>Buy</th>
                        <th>Change</th>
                        <th>High</th>
                        <th>Low</th>
                        <th>Open</th>
                        <th>Close</th>
                        <th>Volume</th>
                    </thead>
                    <tbody>

                        <?php

                        $users = new DMSTOCKSUSERS();
                        $watchlist = $users->getWatchList();

                        if(count($watchlist)):
                            foreach ($watchlist as $key => $watch) {
                                ?>
                                <tr data-symbol="<?=$watch->symbol?>" class="stock-row" >
                                    <td class="value-subdetails">
                                        <label class="lead-text"><?=$watch->symbol?><i class="fa pull-right fa-bar-chart-o text-gray toggle-chart-info"></i></label>
                                        <small class="help-text">Loading data...</small>
                                    </td>
                                    <td colspan="9">
                                        <div class="progress progress-xxs margin-top-20 " style="width: 30%; margin-right: auto; margin-left: auto;">
                                            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                                <span class="sr-only">100% Complete</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            <?php
                            else :
                                ?>
                                <tr class="watchlist-empty">
                                    <td colspan="10" class="text-center"> <h4>Start your watchlist by choosing stock symbols</h4><a href="#" data-toggle="modal" data-target=".add-ticker-symbol">Click here to start adding</a></td>
                                </tr>
                                <?php
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>

        </div>
    </div>
