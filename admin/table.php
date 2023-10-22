<?php
include "functions.php";
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (!isset($_SESSION['hash'])) { exit; }

$joinQuery = "";

if ($_GET["id"] == "mag_events") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "manage_events"))) { exit; }
    $table = 'mag_events';
    $get = $_GET["id"];
    $primaryKey = 'id';
    $extraWhere = "";

    $columns = array(
        array('db' => 'send_time', 'dt' => 0,
            'formatter' => function( $d, $row ) {
                return date("Y-m-d H:i:s", $d);
            }
        ),
        array('db' => 'status', 'dt' => 1),
        array('db' => 'mag_device_id', 'dt' => 2,
            'formatter' => function( $d, $row ) {
                return base64_decode(getMag($d)["mac"]);
            }
        ),
        array('db' => 'event', 'dt' => 3),
        array('db' => 'msg', 'dt' => 4),
        array('db' => 'id', 'dt' => 5,
            'formatter' => function( $d, $row ) {
                $rButtons = '<button data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" type="button" class="btn btn-light waves-effect waves-light btn-xs" onClick="api('.$d.', \'delete\');"><i class="mdi mdi-close"></i></button>';
                return $rButtons;
            }
        )
    );
} else if ($_GET["id"] == "bouquets_streams") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "bouquets"))) { exit; }
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "(`type` = 1 OR `type` = 3) AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "(`type` = 1 OR `type` = 3)";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'category_id', 'dt' => 2,
            'formatter' => function( $d, $row) {
                global $rCategories;
                return $rCategories[$d]["category_name"];
            }
        ),
        array('db' => 'id', 'dt' => 3,
            'formatter' => function( $d, $row) {
                return '<div class="btn-group"><button data-id="'.$d.'" data-type="stream" type="button" style="display: none;" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'stream\', true);"><i class="mdi mdi-minus"></i></button>
                <button data-id="'.$d.'" data-type="stream" type="button" style="display: none;" class="btn-add btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'stream\', true);"><i class="mdi mdi-plus"></i></button></div>';
            }
        )
    );
} else if ($_GET["id"] == "streams_short") {
	if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "categories"))) { exit; }
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "(`type` = 1 OR `type` = 3) AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "(`type` = 1 OR `type` = 3)";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'id', 'dt' => 2,
            'formatter' => function( $d, $row) {
				if (hasPermissions("adv", "edit_stream")) {
					return '<a href="./stream.php?id='.$d.'"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Stream" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
				} else {
					return '--';
				}
            }
        )
    );
} else if ($_GET["id"] == "movies_short") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "categories"))) { exit; }
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`type` = 2 AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "`type` = 2";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'id', 'dt' => 2,
            'formatter' => function( $d, $row) {
				if (hasPermissions("adv", "edit_movie")) {
					return '<a href="./movie.php?id='.$d.'"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Movie" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
				} else {
					return '--';
				}
            }
        )
    );
} else if ($_GET["id"] == "radios_short") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "categories"))) { exit; }
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`type` = 4 AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "`type` = 4";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'id', 'dt' => 2,
            'formatter' => function( $d, $row) {
				if (hasPermissions("adv", "edit_radio")) {
					return '<a href="./radio.php?id='.$d.'"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Station" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
				} else {
					return '--';
				}
            }
        )
    );
} else if ($_GET["id"] == "series_short") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "categories"))) { exit; }
    $table = 'series';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'title', 'dt' => 1),
        array('db' => 'id', 'dt' => 2,
            'formatter' => function( $d, $row) {
				if (hasPermissions("adv", "edit_series")) {
					return '<a href="./series.php?id='.$d.'"><button type="button" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit Series" class="btn btn-light waves-effect waves-light btn-xs"><i class="mdi mdi-pencil-outline"></i></button></a>';
				} else {
					return '--';
				}
            }
        )
    );
} else if ($_GET["id"] == "vod_selection") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "create_channel"))) { exit; }
    $rCategoriesVOD = getCategories("movie");
    $rSeriesList = getEpisodeParents();
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $rSplit = explode(":", $_GET["category_id"]);
        if (intval($rSplit[0]) == 0) {
            $extraWhere = "`type` = 2 AND `category_id` = ".intval($rSplit[1]);
        } else {
            $rEpisodeList = Array();
            foreach ($rSeriesList as $rID => $rRow) {
                if (intval($rSplit[1]) == intval($rRow["id"])) {
                    $rEpisodeList[] = $rID;
                }
            }
            $extraWhere = "`type` = 5 AND `id` IN (".join(",", $rEpisodeList).")";
        }
    } else {
        $extraWhere = "`type` IN (2,5)";
    }
    $extraWhere .= " AND `stream_source` LIKE '%s:".intval($_GET["server_id"]).":%'";
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'category_id', 'dt' => 2,
            'formatter' => function( $d, $row) {
                global $rCategoriesVOD, $rSeriesList;
                if ($row["type"] == 5) {
                    return $rSeriesList[$row["id"]]["title"];
                } else {
                    return $rCategoriesVOD[$d]["category_name"];
                }
            }
        ),
        array('db' => 'type', 'dt' => 3,
            'formatter' => function( $d, $row) {
                return '<div class="btn-group"><button data-id="'.$row["id"].'" data-type="vod" type="button" style="display: none;" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleSelection('.$row["id"].');"><i class="mdi mdi-minus"></i></button>
                <button data-id="'.$row["id"].'" data-type="vod" type="button" style="display: none;" class="btn-add btn btn-light waves-effect waves-light btn-xs" onClick="toggleSelection('.$row["id"].');"><i class="mdi mdi-plus"></i></button></div>';
            }
        )
    );
} else if ($_GET["id"] == "bouquets_vod") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "bouquets"))) { exit; }
    $rCategoriesVOD = getCategories("movie");
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`type` = 2 AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "`type` = 2";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'category_id', 'dt' => 2,
            'formatter' => function( $d, $row) {
                global $rCategoriesVOD;
                return $rCategoriesVOD[$d]["category_name"];
            }
        ),
        array('db' => 'id', 'dt' => 3,
            'formatter' => function( $d, $row) {
                return '<div class="btn-group"><button data-id="'.$d.'" data-type="vod" type="button" style="display: none;" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'vod\', true);"><i class="mdi mdi-minus"></i></button>
                <button data-id="'.$d.'" data-type="vod" type="button" style="display: none;" class="btn-add btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'vod\', true);"><i class="mdi mdi-plus"></i></button></div>';
            }
        )
    );
} else if ($_GET["id"] == "bouquets_series") {
	if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "bouquets"))) { exit; }
    $rCategoriesVOD = getCategories("series");
    $table = 'series';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'title', 'dt' => 1),
        array('db' => 'category_id', 'dt' => 2,
            'formatter' => function( $d, $row) {
                global $rCategoriesVOD;
                return $rCategoriesVOD[$d]["category_name"];
            }
        ),
        array('db' => 'id', 'dt' => 3,
            'formatter' => function( $d, $row) {
                return '<div class="btn-group"><button data-id="'.$d.'" data-type="series" type="button" style="display: none;" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'series\', true);"><i class="mdi mdi-minus"></i></button>
                <button data-id="'.$d.'" data-type="series" type="button" style="display: none;" class="btn-add btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'series\', true);"><i class="mdi mdi-plus"></i></button></div>';
            }
        )
    );
} else if ($_GET["id"] == "bouquets_radios") {
    if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "bouquets"))) { exit; }
    $rCategoriesVOD = getCategories("radio");
    $table = 'streams';
    $get = $_GET["id"];
    $primaryKey = 'id';
    if ((isset($_GET["category_id"])) && (strlen($_GET["category_id"]) > 0)) {
        $extraWhere = "`type` = 4 AND `category_id` = ".intval($_GET["category_id"]);
    } else {
        $extraWhere = "`type` = 4";
    }
    $columns = array(
        array('db' => 'id', 'dt' => 0),
        array('db' => 'stream_display_name', 'dt' => 1),
        array('db' => 'category_id', 'dt' => 2,
            'formatter' => function( $d, $row) {
                global $rCategoriesVOD;
                return $rCategoriesVOD[$d]["category_name"];
            }
        ),
        array('db' => 'id', 'dt' => 3,
            'formatter' => function( $d, $row) {
                return '<div class="btn-group"><button data-id="'.$d.'" data-type="radios" type="button" style="display: none;" class="btn-remove btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'radios\', true);"><i class="mdi mdi-minus"></i></button>
                <button data-id="'.$d.'" data-type="radios" type="button" style="display: none;" class="btn-add btn btn-light waves-effect waves-light btn-xs" onClick="toggleBouquet('.$d.', \'radios\', true);"><i class="mdi mdi-plus"></i></button></div>';
            }
        )
    );
} else {
    exit;
}

$sql_details = array(
    'user' => $_INFO["db_user"],
    'pass' => $_INFO["db_pass"],
    'db'   => $_INFO["db_name"],
    'host' => $_INFO["host"].":".$_INFO["db_port"]
);
 
class SSP {
    /**
     * Create the data output array for the DataTables rows
     *
     * @param array $columns Column information array
     * @param array $data    Data from the SQL get
     * @param bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     * @return array Formatted data in a row based format
     */
    static function data_output ( $columns, $data, $isJoin = false )
    {
        global $get;
        global $rStreamInformation;
        $out = array();
        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();
            if ($get == "streams") {
                $rStreamInformation[intval($data[$i]["id"])] = getStreams(null, true, Array($data[$i]["id"]))[0];
                if (count($rStreamInformation[intval($data[$i]["id"])]["servers"]) == 0) {
                    $rStreamInformation[intval($data[$i]["id"])]["servers"][] = Array("id" => 0, "active_count" => 0, "stream_text" => "Not Available", "uptime_text" => "--", "actual_status" => 0);
                }
                foreach ($rStreamInformation[intval($data[$i]["id"])]["servers"] as $rServer) {
                    for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                        $column = $columns[$j];
                        // Is there a formatter?
                        if ( isset( $column['formatter'] ) ) {
                            $row[ $column['dt'] ] = ($isJoin) ? $column['formatter']( $data[$i][ $column['field'] ], $data[$i], $rServer ) : $column['formatter']( $data[$i][ $column['db'] ], $data[$i], $rServer );
                        } else if (!isset($column["hide"])) {
                            $row[ $column['dt'] ] = ($isJoin) ? $data[$i][ $columns[$j]['field'] ] : $data[$i][ $columns[$j]['db'] ];
                        }
                    }
                    $out[] = $row;
                }
            } else {
                for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                    $column = $columns[$j];
                    // Is there a formatter?
                    if ( isset( $column['formatter'] ) ) {
                        $row[ $column['dt'] ] = ($isJoin) ? $column['formatter']( $data[$i][ $column['field'] ], $data[$i] ) : $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                    } else if (!isset($column["hide"])) {
                        $row[ $column['dt'] ] = ($isJoin) ? $data[$i][ $columns[$j]['field'] ] : $data[$i][ $columns[$j]['db'] ];
                    }
                }
                $out[] = $row;
            }
        }
        return $out;
    }
    /**
     * Paging
     *
     * Construct the LIMIT clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL limit clause
     */
    static function limit ( $request, $columns )
    {
        $limit = '';
        if ( isset($request['start']) && $request['length'] != -1 ) {
            $limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
        } else {
            $limit = "LIMIT 50";
        }
        return $limit;
    }
    /**
     * Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     *  @return string SQL order by clause
     */
    static function order ( $request, $columns, $isJoin = false )
    {
        $order = '';
        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            $dtColumns = SSP::pluck( $columns, 'dt' );
            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';
                    $orderBy[] = ($isJoin) ? $column['db'].' '.$dir : '`'.$column['db'].'` '.$dir;
                }
            }
            $order = 'ORDER BY '.implode(', ', $orderBy);
        }
        return $order;
    }
    /**
     * Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     *
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here performance on large
     * databases would be very poor
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the sql_exec() function
     *  @param  bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     *  @return string SQL where clause
     */
    static function filter ( $request, $columns, &$bindings, $isJoin = false, $table = null)
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = SSP::pluck( $columns, 'dt' );
        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = $request['search']['value'];
            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                if ( $requestColumn['searchable'] == 'true' ) {
                    if (($column["db"] == "mac") && ($table == "mag_devices")) { $str = base64_encode($str); }
                    $binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                    $globalSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
                }
            }
        }
        // Individual column filtering
        for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search( $requestColumn['data'], $dtColumns );
            $column = $columns[ $columnIdx ];
            $str = $requestColumn['search']['value'];
            if ( $requestColumn['searchable'] == 'true' &&
                $str != '' ) {
                if (($column["db"] == "mac") && ($table == "mag_devices")) { $str = base64_encode($str); }
                $binding = SSP::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                $columnSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
            }
        }
        // Combine the filters into a single string
        $where = '';
        if ( count( $globalSearch ) ) {
            $where = '('.implode(' OR ', $globalSearch).')';
        }
        if ( count( $columnSearch ) ) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
        }
        if ( $where !== '' ) {
            $where = 'WHERE '.$where;
        }
        return $where;
    }
    /**
     * Perform the SQL queries needed for an server-side processing requested,
     * utilising the helper functions of this class, limit(), order() and
     * filter() among others. The returned array is ready to be encoded as JSON
     * in response to an SSP request, or can be modified if needed before
     * sending back to the client.
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $sql_details SQL connection details - see sql_connect()
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  array $joinQuery Join query String
     *  @param  string $extraWhere Where query String
     *  @param  string $groupBy groupBy by any field will apply
     *  @param  string $having HAVING by any condition will apply
     *
     *  @return array  Server-side processing response array
     *
     */
    static function simple ( $request, $sql_details, $table, $primaryKey, $columns, $joinQuery = NULL, $extraWhere = '', $groupBy = '', $having = '')
    {
        $bindings = array();
        $db = SSP::sql_connect( $sql_details );
        // Build the SQL query string from the request
        $limit = SSP::limit( $request, $columns );
        $order = SSP::order( $request, $columns, $joinQuery );
        $where = SSP::filter( $request, $columns, $bindings, $joinQuery, $table);
        // IF Extra where set then set and prepare query
        if($extraWhere)
            $extraWhere = ($where) ? ' AND '.$extraWhere : ' WHERE '.$extraWhere;
        $groupBy = ($groupBy) ? ' GROUP BY '.$groupBy .' ' : '';
        $having = ($having) ? ' HAVING '.$having .' ' : '';
        // Main query to actually get the data
        if($joinQuery){
            $col = SSP::pluck($columns, 'db', $joinQuery);
            $query =  "SELECT SQL_CALC_FOUND_ROWS ".implode(", ", $col)."
             $joinQuery
             $where
             $extraWhere
             $groupBy
       $having
             $order
             $limit";
        }else{
            $query =  "SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             FROM `$table`
             $where
             $extraWhere
             $groupBy
       $having
             $order
             $limit";
        }
        $data = SSP::sql_exec( $db, $bindings,$query);
        // Data set length after filtering
        $resFilterLength = SSP::sql_exec( $db,
            "SELECT FOUND_ROWS()"
        );
        $recordsFiltered = $resFilterLength[0][0];
        // Total data set length
        $resTotalLength = SSP::sql_exec( $db,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table`"
        );
        if ($rPermissions["is_admin"]) {
            $recordsTotal = $resTotalLength[0][0];
        } else {
            $recordsTotal = $recordsFiltered;
        }
        /*
         * Output
         */
        return array(
            "draw"            => intval( $request['draw'] ),
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => SSP::data_output( $columns, $data, $joinQuery )
        );
    }
    /**
     * Connect to the database
     *
     * @param  array $sql_details SQL server connection details array, with the
     *   properties:
     *     * host - host name
     *     * db   - database name
     *     * user - user name
     *     * pass - user password
     * @return resource Database connection handle
     */
    static function sql_connect ( $sql_details )
    {
        try {
            $db = @new PDO(
                "mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
                $sql_details['user'],
                $sql_details['pass'],
                array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
            );
            $db->query("SET NAMES 'utf8'");
        }
        catch (PDOException $e) {
            SSP::fatal(
                "An error occurred while connecting to the database. ".
                "The error reported by the server was: ".$e->getMessage()
            );
        }
        return $db;
    }
    /**
     * Execute an SQL query on the database
     *
     * @param  resource $db  Database handler
     * @param  array    $bindings Array of PDO binding values from bind() to be
     *   used for safely escaping strings. Note that this can be given as the
     *   SQL query string if no bindings are required.
     * @param  string   $sql SQL query to execute.
     * @return array         Result from the query (all rows)
     */
    static function sql_exec ( $db, $bindings, $sql=null )
    {
        // Argument shifting
        if ( $sql === null ) {
            $sql = $bindings;
        }
        $stmt = $db->prepare( $sql );
        //echo $sql;
        // Bind parameters
        if ( is_array( $bindings ) ) {
            for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
                $binding = $bindings[$i];
                $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
            }
        }
        // Execute
        try {
            $stmt->execute();
        }
        catch (PDOException $e) {
            SSP::fatal( "An SQL error occurred: ".$e->getMessage() );
        }
        // Return all
        return $stmt->fetchAll();
    }
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Internal methods
     */
    /**
     * Throw a fatal error.
     *
     * This writes out an error message in a JSON string which DataTables will
     * see and show to the user in the browser.
     *
     * @param  string $msg Message to send to the client
     */
    static function fatal ( $msg )
    {
        echo json_encode( array(
            "error" => $msg
        ) );
        exit(0);
    }
    /**
     * Create a PDO binding key which can be used for escaping variables safely
     * when executing a query with sql_exec()
     *
     * @param  array &$a    Array of bindings
     * @param  *      $val  Value to bind
     * @param  int    $type PDO field type
     * @return string       Bound key to be used in the SQL where this parameter
     *   would be used.
     */
    static function bind ( &$a, $val, $type )
    {
        $key = ':binding_'.count( $a );
        $a[] = array(
            'key' => $key,
            'val' => $val,
            'type' => $type
        );
        return $key;
    }
    /**
     * Pull a particular property from each assoc. array in a numeric array,
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @param  bool  $isJoin  Determine the the JOIN/complex query or simple one
     *  @return array        Array of property values
     */
    static function pluck ( $a, $prop, $isJoin = false )
    {
        $out = array();
        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop]. ' AS '.$a[$i]['as'] : $a[$i][$prop];
        }
        return $out;
    }
}

echo json_encode(SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere));
?>