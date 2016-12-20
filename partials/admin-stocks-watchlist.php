<div class="row">

    <?php if(is_user_logged_in()): ?>
        <!-- tabs -->
        <div class="col-md-3 col-sm-3 nopadding">
            <ul class="nav nav-tabs nav-stacked">
                <li class="active"> <a href="#my-portfolio-tab" data-toggle="tab"> My Portfolio </a> </li>
                <li> <a href="#stock-watchlist-tab" data-toggle="tab"> Stocks Watchlist </a> </li>
                <li> <a href="#transaction-history-tab" data-toggle="tab"> Transaction History </a> </li>
            </ul>
        </div>

        <!-- tabs content -->
        <div class="col-md-9 col-sm-9 nopadding">
            <div class="tab-content tab-stacked">
                <div id="my-portfolio-tab" class="tab-pane active">
                    <?php include DM_STOCKS_PLUGIN_DIR . 'partials/tab-my-portfolio.php'; ?>
                </div>
                <div id="stock-watchlist-tab" class="tab-pane">
                    <?php include DM_STOCKS_PLUGIN_DIR . 'partials/tab-stocks-watchlist.php'; ?>
                </div>
                <div id="transaction-history-tab" class="tab-pane">
                    <?php include DM_STOCKS_PLUGIN_DIR . 'partials/tab-transaction-history.php'; ?>
                </div>
            </div>
        </div>

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
    <?php else: ?>
        <div class="col-xs-12">
            <div class="callout alert alert-success noborder margin-top-60 margin-bottom-60">
                <div class="text-center">
                    <h3>You must be logged in first to view your account</h3>
                    <p class="font-lato size-20">
                        Don't have an account yet? <a href="#">Sign up now</a>
                    </p>
                    <a href="/accounts/login"  class="btn btn-success btn-lg margin-top-30">LOGIN NOW</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
