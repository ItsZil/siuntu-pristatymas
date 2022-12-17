<?php
	$CSS_ADDONS = array(
        "datatables" => <<<HTML
            <!-- DataTables -->
            <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
            <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
            <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
            HTML,
	);

    function getHeader(string $title = "Untitled", array $addons = []): string
	{
		global $CSS_ADDONS;
		$header = <<<HTML
        <head>
            <meta charset="utf-8">
            <meta name="description" content="Siuntų pristatymų tarnybos sistema">
            <meta name="author" content="Siuntų pristatymo komanda">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            
            <link rel="shortcut icon" href="/assets/img/favicon.png">
            
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
            
            <!-- Google Font: Source Sans Pro -->
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
            
            <!-- Main stylesheet -->
            <link rel="stylesheet" href="assets/css/style.css">
            
            <!-- Font Awesome  --> 		
            <script src="https://kit.fontawesome.com/d3e1e2eb6a.js" crossorigin="anonymous"></script>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        HTML;

		// For every addon in the addons array, get all the links from $CSS_ADDONS and append to header.
		foreach ($addons as $addon) { $header .= $CSS_ADDONS[$addon]; }

		// Append the title and close the header.
		return $header . <<<HTML
			<title>Siuntos - $title</title>
		</head>
		HTML;
	}