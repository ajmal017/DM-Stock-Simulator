<?php if(!defined('DM_STOCKS_VERSION')) die('Fatal Error');

/*
* Divest Media Stocks Simulator Main Class File
*/
if(!class_exists('DMSTOCKS')){

    class DMSTOCKS
    {
        public $option_fields = [];

        function __CONSTRUCT(){
            add_action('init', [&$this, 'init']);
            add_action('admin_init', [&$this, 'admin_init']);
        }

        public function init(){

            // Pages

            add_action('admin_menu',function(){
                add_menu_page('Stocks Database','Stocks Database','manage_options','stocks-database',function(){
                    include DM_STOCKS_PLUGIN_DIR . 'partials/admin-stocks-management.php';
                },'dashicons-chart-area',76);
            });

            // Ajax Functions

            add_action( 'wp_ajax_stock_ticker_search_symbol', [&$this,'stock_ticker_search'] );
            add_action( 'wp_ajax_nopriv_stock_ticker_search_symbol', [&$this,'stock_ticker_search'] );

            add_action( 'wp_ajax_get_stock_history', [&$this,'get_stock_history'] );
            add_action( 'wp_ajax_nopriv_get_stock_history', [&$this,'get_stock_history'] );

            add_action( 'wp_ajax_get_stock_data', [&$this,'get_stock_data'] );
            add_action( 'wp_ajax_nopriv_get_stock_data', [&$this,'get_stock_data'] );

            add_action( 'wp_ajax_save_stock_data', [&$this,'save_stock_data'] );
            add_action( 'wp_ajax_nopriv_save_stock_data', [&$this,'save_stock_data'] );

            add_action( 'wp_ajax_updateStockData', [&$this,'updateStockData'] );
            add_action( 'wp_ajax_nopriv_updateStockData', [&$this,'updateStockData'] );

            add_action( 'wp_ajax_add_stock_data_to_user', [&$this,'add_stock_data_to_user'] );
            add_action( 'wp_ajax_nopriv_add_stock_data_to_user', [&$this,'add_stock_data_to_user'] );


            // Shortcodes

            add_shortcode( 'stockswatchlist', function($atts){
                wp_enqueue_style('client-stockswatchlist-css', DM_STOCKS_PLUGIN_URL . 'assets/watchlist.css', false, null);

                wp_register_script('client-stocks-sparklines-js', 'http://omnipotent.net/jquery.sparkline/2.1.2/jquery.sparkline.min.js', ['jquery'] );
                wp_enqueue_script('client-stocks-sparklines-js');

                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_script('jquery-ui-autocomplete');

                global $wp_scripts;
                // get registered script object for jquery-ui
                $ui = $wp_scripts->query('jquery-ui-core');
                // tell WordPress to load the Smoothness theme from Google CDN
                $protocol = is_ssl() ? 'https' : 'http';
                $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
                wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

                wp_register_script('client-stocks-widget', 'https://d33t3vvu2t2yu5.cloudfront.net/tv.js', ['jquery'] );
                wp_enqueue_script('client-stocks-widget');

                wp_register_script('client-stocks-watchlist-js', DM_STOCKS_PLUGIN_URL . 'assets/watchlist.js', ['jquery'] );
                wp_enqueue_script('client-stocks-watchlist-js');


                wp_enqueue_style('admin-amcharts-style', 'https://www.amcharts.com/lib/3/plugins/export/export.css', false, null);

                $scriptsPack = [
                    'https://www.amcharts.com/lib/3/amcharts.js',
                    'https://www.amcharts.com/lib/3/serial.js',
                    'https://www.amcharts.com/lib/3/amstock.js',
                    'https://www.amcharts.com/lib/3/plugins/export/export.min.js',
                    'https://www.amcharts.com/lib/3/themes/light.js',
                ];

                foreach ($scriptsPack as $key => $url) {
                    wp_register_script('admin-amcharts-script' . ($key+1), $url , [] );
                    wp_enqueue_script('admin-amcharts-script' . ($key+1));
                }


                include DM_STOCKS_PLUGIN_DIR . 'partials/admin-stocks-watchlist.php';
            });
        }

        public function save_stock_data(){
            global $wpdb;
            global $table_prefix;
            $table  = $table_prefix . 'dm_quotes';
            $symbol = $_POST['s'];
            $symbolCount = 0;
            if(!empty($symbol)){
                // Check if symbol already EXISTS
                $symbolCount = (int)$wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$table." WHERE `symbol` = %s",  $symbol) );

            }

            $quote = false;

            if($symbolCount<1){
                $stockData = $this->prepareStockData($symbol);
            }else{
                exit("exist");
            }


            if(!empty($stockData)){
                $quote = [
                    'symbol' => $stockData['details']->symbol,
                    'name' => $stockData['details']->name,
                    'issuer' => json_encode([
                        'name' => $stockData['details']->issuer_name,
                        'lang' => $stockData['details']->issuer_name_lang,
                    ]),
                    'type' => $stockData['details']->type,
                    'recordts' => json_encode([
                        'utc' => $stockData['details']->utctime,
                        'ts' => $stockData['details']->ts,
                    ]),
                    'statistics' => json_encode([
                        'change' => (float)$stockData['details']->change,
                        'change_percent' => (float)$stockData['details']->chg_percent,
                        'day_high' => (float)$stockData['details']->day_high,
                        'day_low' => (float)$stockData['details']->day_low,
                        'year_high' => (float)$stockData['details']->year_high,
                        'year_low' => (float)$stockData['details']->year_low,
                        'volume' => (float)$stockData['details']->volume,
                        'price' => (float)$stockData['details']->price,
                    ]),
                    'profile' => json_encode($stockData['profile'])
                ];

            }

            if(!empty($quote)){

                $symbolID = $wpdb->insert($table,$quote);
                exit($symbolID);
            }

            exit('0');
        }

        public function get_stock_history(){
            header('Content-type: application/json');

            global $wpdb;
            global $table_prefix;
            $table  = $table_prefix . 'dm_quotes_data';
            $data = [];

            $symbol = $_POST['s'];

            if(!empty($symbol)):
                $data = $wpdb->get_results( $wpdb->prepare("SELECT `date`, `open`, `high`, `low`, `close`, `volume` FROM ".$table." WHERE `symbol` = %s ORDER BY `date` DESC LIMIT 2000",  $symbol) );

                $data = array_reverse($data);
            endif;

            exit(json_encode($data));
        }

        public function get_stock_data(){
            header('Content-type: application/json');
            global $wpdb;
            global $table_prefix;
            $table  = $table_prefix . 'dm_quotes_data';
            $quote = false;
            $symbol = $_POST['s'];
            $stockData = $this->prepareStockData($symbol);

            if(!empty($stockData)){
                $quote = [
                    'symbol' => $stockData['details']->symbol,
                    'name' => $stockData['details']->name,
                    'issuer' => json_encode([
                        'name' => $stockData['details']->issuer_name,
                        'lang' => $stockData['details']->issuer_name_lang,
                    ]),
                    'type' => $stockData['details']->type,
                    'recordts' => json_encode([
                        'utc' => $stockData['details']->utctime,
                        'ts' => $stockData['details']->ts,
                    ]),
                    'statistics' => json_encode([
                        'change' => $stockData['details']->change,
                        'change_percent' => $stockData['details']->chg_percent,
                        'day_high' => $stockData['details']->day_high,
                        'day_low' => $stockData['details']->day_low,
                        'year_high' => $stockData['details']->year_high,
                        'year_low' => $stockData['details']->year_low,
                        'volume' => $stockData['details']->volume,
                        'price' => $stockData['details']->price,
                    ]),
                    'profile' => json_encode($stockData['profile']),
                ];

                // Get Last 10 History

                $quote['history'] = $wpdb->get_results( $wpdb->prepare("SELECT `date`, `open`, `high`, `low`, `close`, `volume` FROM ".$table." WHERE `symbol` = %s ORDER BY `date` DESC LIMIT 20",  $symbol) );

                $quote['history'] = array_reverse($quote['history']);

            }

            exit(json_encode($quote));
        }

        public function stock_ticker_search(){

            header('Content-type: application/json');
            $query = $_GET['s'];
            $data = [];
            $results = $this->file_get_contents_curl('http://d.yimg.com/autoc.finance.yahoo.com/autoc?region=us&lang=en&query='.$query);

            if(!empty($results)){
                $results = json_decode($results);

                if(count($results->ResultSet->Result)){
                    foreach ($results->ResultSet->Result as $result) {

                        if(!in_array($result->type,['S'])) continue;

                        $data[] = [
                            'value' => $result->symbol,
                            'label' => $result->name
                        ];

                    }
                }
            }

            exit(json_encode($data));
        }

        public function file_get_contents_curl($url){

            $ch = curl_init();

            curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Linux; Android 6.0.1; MotoG3 Build/MPI24.107-55) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.81 Mobile Safari/537.36");
            // Disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL,$url);
            // Execute
            $result=curl_exec($ch);
            // Closing
            curl_close($ch);

            return $result;
        }

        public function admin_init()
        {
            // Enqueue Styles
            wp_register_style('admin-stocks-management' , DM_STOCKS_PLUGIN_URL . 'assets/admin.css');
            wp_enqueue_style('admin-stocks-management');

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-autocomplete');

            global $wp_scripts;
            // get registered script object for jquery-ui
            $ui = $wp_scripts->query('jquery-ui-core');
            // tell WordPress to load the Smoothness theme from Google CDN
            $protocol = is_ssl() ? 'https' : 'http';
            $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
            wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

            wp_register_script('admin-stocks-widget', 'https://d33t3vvu2t2yu5.cloudfront.net/tv.js', ['jquery'] );
            wp_enqueue_script('admin-stocks-widget');

            wp_register_script('admin-stocks-management', DM_STOCKS_PLUGIN_URL . 'assets/admin.js', ['jquery'] );
            wp_enqueue_script('admin-stocks-management');



            wp_enqueue_style('admin-amcharts-style', 'https://www.amcharts.com/lib/3/plugins/export/export.css', false, null);

            $scriptsPack = [
                'https://www.amcharts.com/lib/3/amcharts.js',
                'https://www.amcharts.com/lib/3/serial.js',
                'https://www.amcharts.com/lib/3/amstock.js',
                'https://www.amcharts.com/lib/3/plugins/export/export.min.js',
                'https://www.amcharts.com/lib/3/themes/light.js',
            ];

            foreach ($scriptsPack as $key => $url) {
                wp_register_script('admin-amcharts-script' . ($key+1), $url , [] );
                wp_enqueue_script('admin-amcharts-script' . ($key+1));
            }

        }

        public function add_stock_data_to_user(){
            $symbol = $_POST['s'];
            if(!empty($symbol)){
                return add_user_meta(get_current_user_id(),'stock-watchlist',[
                    'symbol' => $symbol,
                    'date' => date('Y-m-d H:i:s')
                ]);
            }

            return 0;
        }

        public function prepareStockData($symbol = false){
            if(!$symbol) return false;

            $data = false;

            // Get Primary Details
            $baseURLQuote = 'http://finance.yahoo.com/webservice/v1/symbols/'.$symbol.'/quote?format=json&view=detail';

            $requestURL = add_query_arg([
                'format' => 'json',
                'view' => 'detail'
            ],$baseURLQuote);

            // Get Response Data
            $responseString = $this->fetchCURL($requestURL);

            // Check if response is valid string
            if(!is_string($responseString)) return false;

            // If valid, decode into JSON object
            $responseObject = json_decode($responseString);

            // Check if Symbol Search responded valid Quote Details
            if($responseObject->list->meta->count && !empty($responseObject->list->resources)){
                $data['details'] = $responseObject->list->resources[0]->resource->fields;
            }else{
                // If Symbol not found return immediately
                return false;
            }

            // Get Company Profile
            $baseURLQuote = "https://query2.finance.yahoo.com/v10/finance/quoteSummary/";
            $requestURL = add_query_arg([
                'formatted' => 'true',
                'crumb' => 'HLn18oo0lxL',
                'lang' => 'en-US',
                'region' => 'US',
                'modules' => implode(',',[
                    'summaryProfile',
                    'detail',
                ]),
                'corsDomain' => 'finance.yahoo.com',
            ],$baseURLQuote . $symbol);

            // Get Response Data
            $responseString = $this->fetchCURL($requestURL);

            // Check if response is valid string
            if(!is_string($responseString)) return false;

            // If valid, decode into JSON object
            $responseObject = json_decode($responseString);
            $data['fetched'] = count($responseObject->quoteSummary->result);
            // Check if there is a profile data fetched
            if(count($responseObject->quoteSummary->result)){
                $data['profile'] = $responseObject->quoteSummary->result[0]->summaryProfile;
            }
            return $data;
        }

        public function updateStockData(){
            $sym = $_POST['symbol'];
            $timeLastMonth = strtotime('last year');
            $from = [
                date('m',$timeLastMonth),
                date('d',$timeLastMonth),
                date('Y',$timeLastMonth)
            ];
            $to = [
                date('m'),
                date('d'),
                date('Y')
            ];
            $csvurl = 'http://real-chart.finance.yahoo.com/table.csv?s='.$sym.'&d='.(absint($to[0])-1).'&e='.absint($to[1]).'&f='.absint($to[2]).'&g=d&a='.(absint($from[0])-1).'&b='.absint($from[1]).'&c='.absint($from[2]).'&ignore=.csv';


            $csv = array_map('str_getcsv', file($csvurl));
            array_walk($csv, function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            });
            array_shift($csv); # remove column header
            global $wpdb;
            global $table_prefix;
            $table  = $table_prefix . 'dm_quotes_data';
            foreach ($csv as $key => $data) {
                // Check if Data exist
                $exist = false;
                $dataEntry = false;
                if(!empty($data['Date'])){
                    // Check if symbol already EXISTS
                    $dataEntry = $wpdb->get_var( $wpdb->prepare("SELECT `id` FROM ".$table." WHERE `date` = %s and `symbol` = %s LIMIT 1" ,  $data['Date'] , $sym) );

                    if(!empty($dataEntry)){
                        $exist = true;
                    }


                    // IF NOT EXIST , INSERT
                    if(!$exist){
                        $symbolID = $wpdb->insert($table,[
                            'symbol' => $sym,
                            'date' => $data['Date'],
                            'open' => $data['Open'],
                            'high' => $data['High'],
                            'low' => $data['Low'],
                            'close' => $data['Close'],
                            'volume' => $data['Volume'],
                            'adj_close' => $data['Adj Close'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }else{
                        $symbolID = $wpdb->update($table,[
                            'open' => $data['Open'],
                            'high' => $data['High'],
                            'low' => $data['Low'],
                            'close' => $data['Close'],
                            'volume' => $data['Volume'],
                            'adj_close' => $data['Adj Close'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ],[
                            'id' => $dataEntry,
                            'date' => $data['Date'],
                            'symbol' => $sym
                        ]);
                    }
                }
            }

            exit(1);
        }

        public function fetchCURL($uri = false){
            if(!$uri) return false;
            $data = false;
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Linux; Android 6.0.1; MotoG3 Build/MPI24.107-55) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.81 Mobile Safari/537.36");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$uri);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }

        public function activate(){

            global $wpdb;
            global $table_prefix;

            $table  = $table_prefix . 'dm_quotes';

            // CREATE QUOTES TABLE
            if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table)
            {
                $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
                    `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `symbol` VARCHAR(255),
                    `name` VARCHAR(255),
                    `issuer` TEXT,
                    `type` TINYTEXT,
                    `recordts` TEXT,
                    `statistics` TEXT,
                    `profile` TEXT,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY  (`id`),
                    UNIQUE KEY `symbol` (`symbol`)
                );";

                $wpdb->query($sql);
            }


            $table  = $table_prefix . 'dm_quotes_data';

            // CREATE QUOTES TABLE
            if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table)
            {
                $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
                    `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `symbol` VARCHAR(255),
                    `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `open` float,
                    `high` float,
                    `low` float,
                    `close` float,
                    `volume` float,
                    `adj_close` float,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY  (`id`)
                );";

                $wpdb->query($sql);
            }
        }

    }
}
