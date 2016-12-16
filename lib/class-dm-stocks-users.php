<?php if(!defined('DM_STOCKS_VERSION')) die('Fatal Error');

/*
* Divest Media Stocks Simulator Main Class File
*/
if(!class_exists('DMSTOCKSUSERS')){

    class DMSTOCKSUSERS
    {
        public $option_fields = [];
        public $tables = [];
        public $errors = [];
        public $db = null;

        function __CONSTRUCT(){
            global $wpdb;
            global $table_prefix;
            $this->db = $wpdb;
            $this->tables['users'] = $table_prefix . 'dm_quotes_stocks_users';
            $this->tables['users_buy'] = $table_prefix . 'dm_quotes_stocks_users_buy';
            $this->tables['quotes'] = $table_prefix . 'dm_quotes';

        }


        function addSymbolToUser($symbol){
            if(!empty($symbol)){
                $watchlist = $this->getWatchList();

                if(count($watchlist)):
                    foreach ($watchlist as $k => $watch) {
                        if($watch->symbol === $symbol){
                            $watchlist[$k] = [
                                'symbol' => $symbol,
                                'date' => date('Y-m-d H:i:s')
                            ];

                            return $this->updateWatchList(false,$watchlist);
                            break;
                        }
                    }
                else:
                    $watchlist = [];
                endif;

                $watchlist[] = [
                    'symbol' => $symbol,
                    'date' => date('Y-m-d H:i:s')
                ];

                return $this->updateWatchList(false,$watchlist);
            }
        }

        function updateWatchList($id = false,$watchlist = []){
            if(!is_user_logged_in()){
                $this->errors[] = [
                    'code' => '104',
                    'message' => 'User not logged in'
                ];
                return false;
            }

            if(!$id) $id = get_current_user_id();

            return $this->db->update($this->tables['users'],[
                'watchlist' => json_encode($watchlist)
            ],[
                'userID' => $id
            ]);
        }

        function buy_user_stocks( $id = false, $symbol = null, $amt = 0, $price = 0){

            if(!is_user_logged_in()){
                $this->errors[] = [
                    'code' => '104',
                    'message' => 'User not logged in'
                ];
                return false;
            }

            if(!$id) $id = get_current_user_id();

            $playerID = false;
            // Check If user is logged in and already in table
            if($playerID = $this->isUserExist($id)){

                if(empty($symbol) || empty($amt) || empty($price)){
                    $this->errors[] = [
                        'code' => '103',
                        'message' => 'Insufficient Data'
                    ];
                    return false;
                }

                return $this->db->insert($this->tables['users_buy'],[
                    'userID' => $playerID,
                    'symbol' => $symbol,
                    'amount' => $amt,
                    'price' => $price,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return false;
        }

        function get_transaction_history( $id = false ){

            if(!is_user_logged_in()){
                $this->errors[] = [
                    'code' => '104',
                    'message' => 'User not logged in'
                ];
                return false;
            }

            if(!$id) $id = get_current_user_id();

            $playerID = false;

            // Check If user is logged in and already in table
            if($playerID = $this->isUserExist($id)){

                $transaction = $this->db->get_results( $this->db->prepare("SELECT ub.symbol,q.name,ub.amount,ub.price,ub.created_at as date FROM `".$this->tables['users_buy']."` ub,`".$this->tables['quotes']."` q WHERE ub.userID = %d and q.symbol = ub.symbol ORDER BY ub.created_at DESC LIMIT 100" , $playerID) );

                return $transaction;
            }

            return false;
        }

        function getWatchList($id = false){

            if(!is_user_logged_in()){
                $this->errors[] = [
                    'code' => '104',
                    'message' => 'User not logged in'
                ];
                return false;
            }

            if(!$id) $id = get_current_user_id();

            $playerID = false;
            // Check If user is logged in and already in table
            if(!($playerID = $this->isUserExist($id))){
                // If User not yet in database, create entry
                $playerID = $this->prepareUserData();
            }

            // Get WatchList via PlayerID

            $watchlist = [];

            $watchListValue = $this->db->get_var( $this->db->prepare("SELECT `watchlist` FROM ".$this->tables['users']." WHERE `id` = %d LIMIT 1" , $playerID) );
            if(!empty($watchListValue)){
                $watchlist = json_decode($watchListValue);
            }
            return $watchlist;
        }

        function prepareUserData(){
            return $this->db->insert($this->tables['users'],[
                'userID' => get_current_user_id(),
                'amount' => DM_STOCKS_INITIAL_WALLET,
                'watchlist' => json_decode([]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        function isUserExist($id){

            $id = $this->db->get_var( $this->db->prepare("SELECT `id` FROM ".$this->tables['users']." WHERE `userID` = %d LIMIT 1" , $id) );

            if(!empty($id)){
                return $id;
            }

            return false;
        }

    }
}
