<div class="row">

    <!-- tabs -->
    <div class="col-md-3 col-sm-3 nopadding">
        <ul class="nav nav-tabs nav-stacked">
            <li class="active">
                <a href="#stock-watchlist-tab" data-toggle="tab">
                    Stocks Watchlist
                </a>
            </li>
            <li>
                <a href="#transaction-history-tab" data-toggle="tab">
                    Transaction History
                </a>
            </li>
        </ul>
    </div>

    <!-- tabs content -->
    <div class="col-md-9 col-sm-9 nopadding">
        <div class="tab-content tab-stacked">
            <div id="stock-watchlist-tab" class="tab-pane active">
                <div class="row">

                    <div id="stockswatchlist" class="col-xs-12">

                        <h4>Stocks Watchlist</h4>
                        <?php
                        if(is_user_logged_in()):

                            ?>
                            <div class="btn-group" role="group" aria-label="...">
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target=".add-ticker-symbol">Add Ticker Symbol</button>
                                <div class="modal fade add-ticker-symbol" tabindex="-1" role="dialog" aria-labelledby="modal-ticker-symbol-label" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">

                                            <!-- header modal -->
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="modal-ticker-symbol-label">Add Stock Symbol</h4>
                                            </div>

                                            <!-- body modal -->
                                            <div class="modal-body">
                                                <form>
                                                    <div class="search-box">
                                                        <input type="search" id="ticker_search" class="form-control" placeholder="GOOGL"/>
                                                        <span class="fa fa-spinner fa-spin"></span>
                                                    </div>
                                                    <p id="ticker_search_data" class="margin-top-20">

                                                    </p>
                                                </form>
                                            </div>
                                            <div class="modal-footer"><!-- modal footer -->
                                                <button class="btn btn-default" data-dismiss="modal">Close</button>
                                                <button class="btn btn-primary" id="add-symbol-to-user">Add Symbol</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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

                                        $watchlist = get_user_meta(get_current_user_id(),'stock-watchlist');
                                        $users = new DMSTOCKSUSERS();
                                        $watchlist = $users->getWatchList();

                                        if(count($watchlist)):
                                            foreach ($watchlist as $key => $watch) {
                                                ?>
                                                <tr data-symbol="<?=$watch->symbol?>" class="stock-row">
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
                            <?php else: ?>
                                <div class="callout alert alert-success noborder margin-top-60 margin-bottom-60">
                                    <div class="text-center">
                                        <h3>You must be logged in first to view your account</h3>
                                        <p class="font-lato size-20">
                                            Don't have an account yet? <a href="#">Sign up now</a>
                                        </p>
                                        <a href="/accounts/login"  class="btn btn-success btn-lg margin-top-30">LOGIN NOW</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <div id="transaction-history-tab" class="tab-pane">
                    <h4>Transaction History</h4>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php

                            $users = new DMSTOCKSUSERS();
                            $history = $users->get_transaction_history( false );
                            if(count($history)):
                                foreach ($history as $key => $data):
                                ?>
                                <tr>
                                    <td><?=$data->symbol?></td>
                                    <td><?=$data->name?></td>
                                    <td><?=number_format($data->amount,0)?></td>
                                    <td>$<?=number_format($data->price,2)?></td>
                                    <td><?=date('D M j G:i:s T',strtotime($data->date))?></td>

                                </tr>
                                <?php
                                endforeach;
                            else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">No Transactions Yet</td>
                                </tr>
                                <?php
                            endif;
                            ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
