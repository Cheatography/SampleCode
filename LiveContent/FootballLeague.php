<?php

    /*
     * This script collects an HTML page from the URL defined below, using YQL (a Yahoo API).
     * Yahoo finds elements within the page using XPATH, and returns JSON containing those
     * elements. We then iterate through that data to produce the correct format for
     * Cheatography and output JSON.
     *
     * Some useful tutorials for the items used here:
     *     - XPATH: http://www.w3schools.com/xpath/
     *     - JSON: http://www.w3schools.com/json/
     *
     * For this example, we're going to collect the "Permiership League Table" from:
     *     - http://news.bbc.co.uk/sport1/hi/football/eng_prem/table/default.stm
     *
     * This code is in use live - it currently populates the content for this block:
     *     - http://www.cheatography.com/davechild/content/england-football-premiership/
     * */

    // Define variables for Yahoo. The URL to be collected, and XPATH to find the content.
        $url = "http://news.bbc.co.uk/sport1/hi/football/eng_prem/table/default.stm";
        $xpath = "//table[@class=\\'fulltable\\']//tr[@class=\\'r1\\' or @class=\\'r2\\']"; // Any table row whose class is "r1" or "r2" within any table with class "fulltable".

    // Yahoo Request, including YQL. You shouldn't need to change this.
        $yql = "http://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20html%20WHERE%20url%3D'" . urlencode($url) . "'%20AND%20xpath%3D'" . urlencode($xpath) . "'&format=json";
        $data = json_decode(file_get_contents($yql), true);
        //var_dump($data); die();

    // Initialise the array we're going to return when we finish.
        $cheatography_content = array();

    // The first row of our content is going to be column titles in bold
        $cheatography_content[] = array(
            '**Pos**',
            '**Team**',
            '**P/W/D/L**',
            '**Pts**'
        );

    // Build content block. The content collected will be in $data['query']['results'].
    // In this case, we've collected divs, so we'll loop through them.
        for($i = 0, $max = count($data['query']['results']['tr']); $i < $max; $i++) {
            $_item = $data['query']['results']['tr'][$i];
            //var_dump($_item); die();
            // Add item to content array
            $cheatography_content[] = array(
                ($i + 1),
                $_item['td'][1]['a']['content'],
                $_item['td'][2]['p'] . '/' . ($_item['td'][3]['p'] + $_item['td'][8]['p']) . '/' . ($_item['td'][4]['p'] + $_item['td'][9]['p']) . '/' . ($_item['td'][5]['p'] + $_item['td'][10]['p']),
                $_item['td'][14]['p'],
            );
        }
        //var_dump($cheatography_content); die();

    // We don't want to return data at all if there was an error. We should have lots
    // of teams, so if there are fewer than 10 we return an error. This
    // ensures the content block won't go blank accidentally on Cheatography.
        if (count($cheatography_content) < 10) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            die();
        }

    // If we're still here, we output the content as JSON.
        header('Content-type: application/json');
        echo json_encode($cheatography_content);
