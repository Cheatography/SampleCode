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
     * For this example, we're going to collect the "Latest Comments" data from Cheatography,
     * which is in a block on the right of the homepage.
     *
     * This code is in use live - it currently populates the content for this block:
     *     - http://www.cheatography.com/davechild/content/latest-comments/
     * */

    // Define variables for Yahoo. The URL to be collected, and XPATH to find the content.
        $url = "http://www.cheatography.com/";
        $xpath = "//div[@id=\\'latest_comments\\']//div[contains(@class, \\'sidebar_box_row\\')]"; // Any div whose classes include "sidebar_box_row" within any div with id "latest_comments".

    // Yahoo Request, including YQL. You shouldn't need to change this.
        $yql = "http://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20html%20WHERE%20url%3D'" . urlencode($url) . "'%20AND%20xpath%3D'" . urlencode($xpath) . "'&format=json";
        $data = json_decode(file_get_contents($yql), true);
        //var_dump($data); die();

    // Initialise the array we're going to return when we finish.
        $cheatography_content = array();

    // The first row of our content is going to be column titles in bold
        $cheatography_content[] = array(
            '**Member**',
            '**Comment On**'
        );

    // Build content block. The content collected will be in $data['query']['results'].
    // In this case, we've collected divs, so we'll loop through them.
        for($i = 0, $max = count($data['query']['results']['div']); $i < $max; $i++) {
            $_item = $data['query']['results']['div'][$i];
            // Add item to content array
            $cheatography_content[] = array(
                $_item['a'][1]['content'],
                $_item['p']['a']['content'],
            );
        }
        //var_dump($cheatography_content); die();

    // We don't want to return data at all if there was an error. We should have at
    // least five comments, so if there are fewer than 5 we return an error. This
    // ensures the content block won't go blank accidentally on Cheatography.
        if (count($cheatography_content) < 5) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            die();
        }

    // If we're still here, we output the content as JSON.
        header('Content-type: application/json');
        echo json_encode($cheatography_content);
