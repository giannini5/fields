<?php

/**
 * @brief CSS used for web site.
 */
class View_Styles {
    public function __construct() {
    }

    /**
     * @brief Render the CSS data
     *
     * @param int $facilityCount count of facilities
     */
    public function render($facilityCount) {
        // <link rel="stylesheet" src="css/lightbox.css" type="text/css" media="screen" />
        // <script language="javascript" type="text/javascript" src="js/lightbox.js"></script>

        // Try these when you get a chance:
        // <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        // <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        // Searching for fix to lock problem
        //             <link rel="stylesheet" src="css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet"/>

        print '
            <META charset="utf-8">
            <META HTTP-EQUIV="EXPIRES" CONTENT="Thu, 12 Apr 2007 08:21:57 GMT">

            <script language="javascript" type="text/javascript" src="js/jquery-1.9.1.js"></script>
            <script language="javascript" type="text/javascript" src="js/jquery-ui-1.10.3.custom.js"></script>
            <script src="js/sorttable.js"></script>

            <style>
                body{
                    font: 75% "Trebuchet MS", sans-serif;
                }';
        /*
                .demoHeaders {
                    margin-top: 2em;
                }
                #dialog-link {
                    padding: .4em 1em .4em 20px;
                    text-decoration: none;
                    position: relative;
                }
                #dialog-link span.ui-icon {
                    margin: 0 5px 0 0;
                    position: absolute;
                    left: .2em;
                    top: 50%;
                    margin-top: -8px;
                }
                #icons {
                    margin: 0;
                    padding: 0;
                }
                #icons li {
                    margin: 2px;
                    position: relative;
                    padding: 4px 0;
                    cursor: pointer;
                    float: left;
                    list-style: none;
                }
                #icons span.ui-icon {
                    float: left;
                    margin: 0 4px;
                }
                .fakewindowcontain .ui-widget-overlay {
                    position: absolute;
                }
                #wrap {
                    width: 100%;
                    margin: 0 auto;
                    background-color: #fff;
                }
                .ui-menu { width: 150px; }
                .right-list-top {
                    min-height: 30px;
                    overflow: hidden;
                    width: 29%;
                    text-align: center;
                    vertical-align: middle;

                }
                .left-list-top {
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 70%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-messageContent-top {
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 64%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-previewImage-top {
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 27%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-isActive-top {
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 8%;
                    text-align: center;
                    vertical-align: middle;
                }
                #right-controller {
                    min-height: 700px;
                    overflow: hidden;
                    width: 15%;
                    height: 25em;
                    position: relative;
                }
                #left-controller {
                    float: left;
                    height: 750px;
                    overflow: auto;
                    width: 85%;
                    position: relative;
                }
                #rsmSelect-controller {
                    float: left;
                    height: 750px;
                    overflow: hidden;
                    width: 100%;
                }
                .center{margin:auto; width: 100%; height: 100%;}
                .userAction-import {
                    min-height: 100px;
                    min-width: 10%;
                    overflow: hidden;
                    width: 10%;
                    height: 8em;
                    float: left;
                    line-height: 8em;
                    padding: 10px;
                }
                .rsmInfo {
                    min-height: 100px;
                    overflow: auto;
                    width: 100%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmAttributes-import {
                    min-height: 100px;
                    overflow: auto;
                    width: 58%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmPreview-import {
                    min-height: 100px;
                    overflow: auto;
                    width: 30%;
                    float: right;
                    height: 9em;
                }
                .userAction-select {
                    min-height: 100px;
                    min-width: 10%;
                    overflow: hidden;
                    width: 10%;
                    float: left;
                    padding: 5px;
                }
                .rsmAttributes-select {
                    min-height: 100px;
                    overflow: auto;
                    width: 53%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmPreview-select {
                    min-height: 100px;
                    overflow: auto;
                    width: 27%;
                    float: left;
                    height: 9em;
                }
                .isActive-select {
                    min-height: 100px;
                    overflow: auto;
                    width: 9%;
                    height: 10em;
                    float: left;
                    line-height: 10em;
                    vertical-align: middle;
                    text-align: center;
                }
                .custom-combobox {
                    position: relative;
                    display: inline-block;
                }
                .custom-combobox-toggle {
                    position: absolute;
                    top: 0;
                    bottom: 0;
                    margin-left: -1px;
                    padding: 0;
                    *height: 1.7em;
                    *top: 0.1em;
                }
                .custom-combobox-input {
                    margin: 0;
                    padding: 0.3em;
                }
                .button {
                    min-width: 10px;
                }
                .topDiv {
                    width:100%;
                    background-color: rgb(180,180,255);
                    height: 30px;
                    border-top-left-radius: 10px;
                    border-top-right-radius: 10px;
                    -webkit-border-top-left-radius: 10px;
                    -webkit-border-top-right-radius: 10px;
                    -moz-border-radius-topleft: 10px;
                    -moz-border-radius-topright: 10px;
                    -khtml-border-top-left-radius: 10px;
                    -khtml-border-top-right-radius: 10px;
                }
                .rmsDiv-import{
                    width:100%;
                    overflow: auto;
                    background-color: rgb(240,240,255);
                    border: thin solid rgb(127,127,255);
                    border-radius: 10px;
                    height: 165px;
                    -webkit-border-radius: 10px;
                    -moz-border-radius: 10px;
                    -khtml-border-radius: 10px;
                    box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -webkit-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -moz-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -khtml-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                }
                .rmsDiv-select {
                    width:99%;
                    background-color: rgb(240,240,255);
                    border: thin solid rgb(127,127,255);
                    border-radius: 10px;
                    height: 200px;
                    -webkit-border-radius: 10px;
                    -moz-border-radius: 10px;
                    -khtml-border-radius: 10px;
                    box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -webkit-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -moz-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                    -khtml-box-shadow: 0 5px 12px rgba(0,0,0,.4);
                }

                .attributesTable td {
                    border: none;
                    border-bottom: 1px solid #cecece;
                    height: 20px;
                }
                .title {
                    font-weight: bold;
                    font-size:  small;
                    width:      110px;
                    text-align: left;
                    veritcal-align: bottom;
                }
                .data {
                    font-size: small;
                    color: #777;
                }
                .scrollbar {
                    overflow:auto;
                }
                #fieldSet {
                    overflow:auto;
                    scrollbar-3dlight-color:#FFFFFF;
                    scrollbar-arrow-color:#000000;
                    scrollbar-base-color:#FF9999;
                    scrollbar-darkshadow-color:#000000;
                    scrollbar-face-color:#000000;
                    scrollbar-highlight-color:#000000;
                    scrollbar-shadow-color:#0033CC;
                    height:650px;
                }

                input[type=file] {
                    text-shadow: 0 1px 1px rgba(255,255,255,0.75);
                    vertical-align: middle;
                    cursor: pointer;
                    background-color: #f5f5f5;
                    background-image: -moz-linear-gradient(top,#fff,#e6e6e6);
                    background-image: -webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6));
                    background-image: -webkit-linear-gradient(top,#fff,#e6e6e6);
                    background-image: -o-linear-gradient(top,#fff,#e6e6e6);
                    background-image: linear-gradient(to bottom,#fff,#e6e6e6);
                    background-repeat: repeat-x;
                    border: 1px solid #ccc;
                    border-color: #e6e6e6 #e6e6e6 #bfbfbf;
                    border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);
                    border-bottom-color: #b3b3b3;
                    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ffffffff",endColorstr="#ffe6e6e6",GradientType=0);
                    filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
                    -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                    -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                }

                input[type=radio] + label {
                    display:inline-block;
                    margin:-2px;
                    padding: 4px 12px;
                    margin-bottom: 0;
                    font-size: 14px;
                    line-height: 20px;
                    color: #333;
                    text-align: center;
                    text-shadow: 0 1px 1px rgba(255,255,255,0.75);
                    vertical-align: middle;
                    cursor: pointer;
                    background-color: #f5f5f5;
                    background-image: -moz-linear-gradient(top,#fff,#e6e6e6);
                    background-image: -webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6));
                    background-image: -webkit-linear-gradient(top,#fff,#e6e6e6);
                    background-image: -o-linear-gradient(top,#fff,#e6e6e6);
                    background-image: linear-gradient(to bottom,#fff,#e6e6e6);
                    background-repeat: repeat-x;
                    border: 1px solid #ccc;
                    border-color: #e6e6e6 #e6e6e6 #bfbfbf;
                    border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);
                    border-bottom-color: #b3b3b3;
                    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ffffffff",endColorstr="#ffe6e6e6",GradientType=0);
                    filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
                    -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                    -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
                }

                input[type=radio]:checked + label {
                    background-image: none;
                    outline: 0;
                    -webkit-box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    -moz-box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    background-color:#669fff;
                }
        */
        print '
                #nav {
                    width: 100%;
                    float: left;
                    margin: 0 0 1em 0;
                    padding: 0;
                    list-style: none;
                    background-color: #f2f2f2;
                    border-bottom: 1px solid #ccc;
                    border-top: 1px solid #ccc; }
                #nav li {
                    float: left; }
                #nav li a {
                    display: block;
                    padding: 8px 15px;
                    text-decoration: none;
                    font-weight: bold;
                    color: #069;
                    border-right: 1px solid #ccc; }
                #nav li a:hover {
                    color: #c00;
                    background-color: #fff; }
                #nav li select{
                    color: #c00;
                    background-color: #fff; }
                #nav li div{
                    display: block;
                    padding: 8px 15px;
                    text-decoration: none;
                    font-weight: bold;
                    color: #c00;
                    background-color: #fff;
                    border-right: 1px solid #ccc; }';
        /*

                body {
                    background-color: lightskyblue;
                    font: small/1.3 Arial, Helvetica, sans-serif; }
                h1 {
                    font-size: 1.2em;
                    padding: 1em 10px;
                    color: #069;
                }
                #content {
                    padding: 0 20px 20px; }

                #leftHeader {
                    float: left;
                    width: 75px;
                    height: 100%
                }
                #rightHeader {
                    height: 75px;
                    background-color: #fff;
                    vertical-align: middle;
                    padding-left: 75px;
                    padding-top: 5px;
                }
                #rsmEdit-top-left {
                    width:52%;
                    background-color: rgb(240,240,255);
                    border: thin solid rgb(127,127,255);
                    height: 65px;
                    text-align: center;
                    float: left;
                    padding-top: 20px;
                }
                #rsmEdit-top-right {
                    width:50%;
                    background-color: rgb(240,240,255);
                    height: 80px;
                    text-align: center;
                    float: right;
                    padding-top: 5px;
                }
                #rsmEdit-bottom {
                    width:100%;
                    background-color: rgb(240,240,255);
                    height: 80px;
                    text-align: center;
                    float: right;
                    padding-top: 5px;
                }
                #rsmEdit-left {
                    width: 55%;
                    min-width: 55%;
                    float: left;
                    min-height: 750px;
                    position: relative;
                    overflow: auto;
                }
                #rsmEdit-right {
                    width: 45%;
                    min-width: 45%;
                    float: right;
                    min-height: 750px;
                    overflow: auto;
                }
                #rsmEditAction {
                    overflow: hidden;
                    background-color: rgb(240,240,255);
                    border: thin solid rgb(127,127,255);
                    padding: 10px;
                    margin-top: 20px;
                    margin: 10px;
                }
                #rsmTest-left {
                    width: 55%;
                    float: left;
                    min-height: 550px;
                }
                #rsmTest-right {
                    width: 45%;
                    min-height: 550px;
                    overflow: hidden;
                }
                #mediaIds {
                    overflow:auto;
                    height: 150px;
                }
                .button#saveRSM, .button#updateRMS, .button#reset{
                    width: 300px;
                }

                .error{
                    color:red;
                    padding-left: 5px;
                    font-size: 12pt;
                }

                .errorClass{
                    color:red;
                    padding-left: 5px;
                    font-size: 12pt;
                }

                .successClass{
                    color:green;
                    padding-left: 20px;
                    font-size: 16pt;
                }
                .alert {
                    padding: 8px 35px 8px 14px;
                    margin-bottom: 10px;
                    text-shadow: 0 1px 0 rgba(255,255,255,0.5);
                    background-color: #fcf8e3;
                    border: 1px solid #fbeed5;
                    -webkit-border-radius: 4px;
                    -moz-border-radius: 4px;
                    border-radius: 4px;
                }
                .alert-error {
                    color: #b94a48;
                    background-color: #f2dede;
                    border-color: #eed3d7;
                }
        */

        print '
            </style>';

        /*
            <style>
                table.sortable th {
                    color:#0000FF;
                    cursor:pointer;
                    text-decoration:underline;
                }
                table.sortable tbody tr:nth-child(2n) td {
                  background: #eeeeee;
                }
                table.sortable tbody tr:nth-child(2n+1) td {
                  background: #ffffff;
                }
            </style>';
        */

        // $this->_collapsibleCSS($facilityCount);
        // $this->_collapsibleJS($facilityCount);
        $this->_accordion();
    }

    /**
     * @brief Render the CSS needed to mark a table collapsible and
     *        start in the collapsed position.
     *
     * @param $facilityCount - Count of facilities
     */
    private function _collapsibleCSS($facilityCount) {
        print "
            <style>
                table, tr, td, th
                {
                    border-collapse:collapse;
                }";

        for ($index = 1; $index <= $facilityCount; $index++) {
            print "
                .expandContract$index
                {
                    cursor:pointer;
                    text-decoration: underline;
                    color: darkblue;
                    font-size: 125%;
                }";
        }

        for ($index = 1; $index <= $facilityCount; $index++) {
            print "
                .collapsible$index { display: none;
                }";
        }

        print "
            </style>";
    }

    private function _collapsibleJS($facilityCount) {
        print '
            <script>
                $(document).ready(function(){';

        for ($index = 1; $index <= $facilityCount; $index++) {
            print '
                    $(".expandContract' . $index . '").click(function(){
                        $(".collapsible' . $index . '").toggle(500);
                    });';
        }

        print "
                });
            </script>";
    }

    private function _accordion() {
        // <link rel='stylesheet' type='text/css' href='/apps/test_harness/css/style.css'>
        //         <script type='text/javascript' src='http://cdn.jquerytools.org/1.2.5/full/jquery.tools.min.js'></script>
        //         <link rel='stylesheet' type='text/css' href='http://static.flowplayer.org/tools/css/standalone.css'>



        print "
        <script type='text/javascript' src='http://code.jquery.com/jquery-1.6.2.js'></script>
        <style type='text/css'>
            /* root element for accordion. decorated with rounded borders and gradient background image */
            .accordion {
                background:#ffffff;
                margin: auto;
                position: relative;
                width: 635px;
                border:1px solid #333;
                /* -background:#666; */
            }

            /* accordion header */
            .accordion h2 {
                /* background:#99ccff; */
                margin:0;
                padding:5px 15px;
                font-size:14px;
                font-weight:normal;
                border:1px solid #fff;
                border-bottom:1px solid #ddd;
                cursor:pointer;
                : #000;
            }

            /* currently active header */
            .accordion h2.current {
              cursor:default;
              background-color:#6699ff;
            }

            /* accordion pane */
            .accordion .pane {
              display:none;
              padding:15px;
              color:#333;
              font-size:12px;
            }

            /* a title inside pane */
            .accordion .pane h3 {
              font-weight:normal;
              margin:0 0 -5px 0;
              font-size:16px;
              color:#999;
            }
            
            /* root element for noaccordion. decorated with rounded borders and gradient background image */
            .noaccordion {
                background:#ffffff;
                margin: auto;
                position: relative;
                width: 635px;
                border:1px solid #333;
                /* -background:#666; */
            }

            /* noaccordion header */
            .noaccordion h2 {
                /* background:#99ccff; */
                margin:0;
                padding:5px 15px;
                font-size:14px;
                font-weight:normal;
                border:1px solid #fff;
                border-bottom:1px solid #ddd;
                cursor:pointer;
                : #000;
            }
        </style>

        <script type='text/javascript'>//<![CDATA[

            $(document).ready(function() {
              //The click to hide function
              $('.accordion > h2').click(function() {
                if ($(this).hasClass('current') && $(this).next().queue().length === 0) {
                  $(this).next().slideUp();
                  $(this).removeClass('current');
                } else if (!$(this).hasClass('current') && $(this).next().queue().length === 0) {
                  $(this).next().slideDown();
                  $(this).addClass('current');
                }
              });
            });
            //]]>
        </script>
        ";
    }
}
