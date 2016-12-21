<?php

$users = new DMSTOCKSUSERS();
$history = $users->get_transaction_history( false );

function negatePriceValue($value,$prefix = '',$suffix = '',$precision = 2,$class = false){

    $negate = (float)($value) < 0;

    if($negate){
        return "(-" . $prefix . number_format(abs($value),$precision) . $suffix . ")";
    }

    return $prefix . number_format(abs($value),$precision) . $suffix ;
}

?>

<h4>Transaction History</h4>
<table class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>Symbol</th>
            <th>Name</th>
            <th>Amount</th>
            <th>Purchase Price</th>
            <th>Current Price</th>
            <th>Gain</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>

        <?php



        if($history && count($history)>0):
            foreach ($history as $key => $data):
                ?>
                <tr>
                    <td><?=$data->symbol?></td>
                    <td><?=$data->name?></td>
                    <td class="text-right"><?=negatePriceValue($data->amount,'$','',0)?></td>
                    <td class="text-right"><?=negatePriceValue($data->price,'$')?></td>
                    <td class="text-right"><?=negatePriceValue($data->now,'$')?></td>
                    <td class="text-right"><?=negatePriceValue(($data->now - $data->price) * $data->amount,'$')?><br><small><?=negatePriceValue((($data->now - $data->price) / ($data->price))*100,'','%')?></small></td>
                    <td><?=date('D M j G:i:s T',strtotime($data->date))?></td>
                </tr>
                <?php
            endforeach;
        else:
            ?>
            <tr>
                <td colspan="7" class="text-center">No Transactions Yet</td>
            </tr>
            <?php
        endif;
        ?>


    </tbody>
</table>
