<?php
    $total = $numbers['num_plants'];
    
    if ($start >= $rows) {
        $limit = $rows;
        $offset = 0;
        $title = 'First page';
        $first = '<a href="' . $url . 'rows=' . 
            $limit . '&start=' . $offset . '" class="ui-state-default" title="' . $title .'">' .
            '<span class="glyphicon glyphicon-fast-backward"></span></a>';

        $offset = $start-$rows;
        $title = 'Previous page';
        $prev = '<a href="' . $url . 'rows=' . 
            $limit . '&start=' . $offset . '" class="ui-state-default" title="' . $title .'">' .
            '<span class="glyphicon glyphicon-triangle-left"></span></a>';
    }
    else {
        $first = '<span class="glyphicon glyphicon-fast-backward"></span>';
        $prev = '<span class="glyphicon glyphicon-triangle-left"></span>';
    }
    
    $end = ($start+$rows > $total) ? $total : $start+$rows;
    $current = $start+1 . '–' . $end . ' of ' . $total;

    if ($start+$rows < $total) {
        $limit = $rows;
        $offset = $start+$rows;
        $title = 'Next page';
        $next = '<a href="' . $url . 'rows=' . 
            $limit . '&start=' . $offset . '" class="ui-state-default" title="' . $title .'">' .
            '<span class="glyphicon glyphicon-step-forward"></span></a>';

        $offset = floor(($total/$rows))*$rows;
        $title = 'Last page';
        $last = '<a href="' . $url . 'rows=' . 
            $limit . '&start=' . $offset . '" class="ui-state-default" title="' . $title .'">' .
            '<span class="glyphicon glyphicon-fast-forward"></span></a>';
    }
    else {
        $next = '<span class="glyphicon glyphicon-triangle-right"></span>';
        $last = '<span class="glyphicon glyphicon-fast-forward"></span>';
    }
?>