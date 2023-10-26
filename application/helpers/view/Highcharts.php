<?php

/**
 * Helper class to create required layouts
 * 
 * @author Harpreet Singh
 * @date   27 May, 2014
 * @version 1.0
 */

class Damco_View_Helper_Highcharts {
    
    /**
     * Constructor to initialize Layouts class
     */
    function __construct( ) {
    }

    public function highcharts( $chartSettings = array(), $xAxis_categories = '', $seriesData = array() ) {
        
        // Missing properties can be addedby referencing to the Highchart API at below mentioned URL
        // http://api.highcharts.com/highcharts#chart
        
        
        $chartData = '';
        $chartData .= '<script type="text/javascript" src="http://triumph.360osi.com/js/report_eventtype_highcharts.js"></script>'
                . '<!-- <script type="text/javascript" src="http://triumph.360osi.com/js/report_eventtype_exporting.js"></script> -->';
        
    
        $chartData .= "<script>

            $(function() {
                $('#".$chartSettings['div_id']."').highcharts({
                    chart: {
                        type: '".$chartSettings['chart_type']."',
                        height : '".$chartSettings['chart_height']."'
                    },
                    title: {
                        text: '".$chartSettings['title_text']."'
                    },
                    xAxis: {
                        categories: [".$xAxis_categories."]
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '".$chartSettings['yAxis_title_text']."'
                        }
                    },
                    legend: {
                        reversed: true,
                        enabled: '".$chartSettings['legend_enabled']."'
                    },
                    colors: [".$chartSettings['colors']."],
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                    series: [ ".$seriesData." ],
                    tooltip: {
                        enabled: '".$chartSettings['tooltip_enabled']."'
                    }
                });
            });
        </script>";
        
        return $chartData;
    }
}