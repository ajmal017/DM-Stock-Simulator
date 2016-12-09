<?php

if(empty($_GET['symbol'])):
    ?>

    <div class="wrap">
        <nav class="nav-toolbar">
            <ul>
                <li>
                    <button id="add-stocks" class="button button-primary"><span class="dashicons dashicons-search"></span>
                        Add Ticker Symbol</button>
                    </li>
                </ul>
            </nav>
            <hr />
            <div id="member-table">
                <div class="metabox-holder columns-2">
                    <div class="meta-box-sortables ui-sortable">
                        <form method="post">
                            <?php
                            $stocktable = new Stocks_List();
                            $stocktable->prepare_items();
                            $stocktable->display();
                            ?>
                            <?php wp_nonce_field( 'delete_stock' , 'dm_delete_stock' ) ?>
                        </form>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>

        <div id="dialog-form" title="Add New Ticker Symbol">
            <form>
                <fieldset>
                    <label for="name">Symbol</label>
                    <input type="text" name="ticker_symbol" id="ticker_symbol" value="" placeholder="e.g GOOGL" class="text ui-widget-content ui-corner-none" required="">
                    <p id="ticker_data">

                    </p>
                    <!-- Allow form submission with keyboard without duplicating the dialog button -->
                    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
                </fieldset>
            </form>
        </div>
        <?php
    else:
        ?>
        <div class="wrap">
            <a href="?page=stocks-database" class="button button-primary">Back to Stocks List</a>
            <hr />
            <p style="float: right; width: 50%; max-height: 220px; overflow: auto;">
                <label style="font-weight:bold;">Summary: </label><br>
                <span class="stock-data-summary">-</span>
            </p>
            <label style="font-weight:bold;">Symbol: </label><span class="stock-data-symbol">-</span><br>
            <label style="font-weight:bold;">Name: </label><span class="stock-data-name">-</span><br>
            <label style="font-weight:bold;">Type: </label><span class="stock-data-type">-</span><br>
            <label style="font-weight:bold;">Address: </label><span class="stock-data-address">-</span><br>
            <label style="font-weight:bold;">City: </label><span class="stock-data-city">-</span><br>
            <label style="font-weight:bold;">State: </label><span class="stock-data-state">-</span><br>
            <label style="font-weight:bold;">Country </label><span class="stock-data-country">-</span><br>
            <label style="font-weight:bold;">ZIP: </label><span class="stock-data-zip">-</span><br>
            <label style="font-weight:bold;">Phone: </label><span class="stock-data-phone">-</span><br>
            <label style="font-weight:bold;">Website: </label><span class="stock-data-website">-</span><br>
            <label style="font-weight:bold;">Industry: </label><span class="stock-data-industry">-</span><br>
            <label style="font-weight:bold;">Sector: </label><span class="stock-data-sector">-</span><br>
            <label style="font-weight:bold;">Employees: </label><span class="stock-data-employees">-</span><br>

            <style>
            #stockChartDataDiv {
                width: 100%;
                height: 450px;
                vertical-align: top;
                display: inline-block;
            }
            </style>
            <hr />
            <div id="stockChartDataDiv" data-symbol="<?=$_GET['symbol']?>"></div>
        </div>
        <?php
    endif;
    ?>
