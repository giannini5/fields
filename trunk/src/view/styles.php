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


        print '
            <META charset="utf-8">
            <META HTTP-EQUIV="EXPIRES" CONTENT="Thu, 12 Apr 2007 08:21:57 GMT">
            <link rel="stylesheet" src="css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet"/>

            <script language="javascript" type="text/javascript" src="js/jquery-1.9.1.js"></script>
            <script language="javascript" type="text/javascript" src="js/jquery-ui-1.10.3.custom.js"></script>
            <script src="js/sorttable.js"></script>

            <style>
                body{
                    font: 75% "Trebuchet MS", sans-serif;
                }
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
                    /*background-color: red;
                    background-color: rgb(180,180,255);*/
                    min-height: 30px;
                    overflow: hidden;
                    width: 29%;
                    text-align: center;
                    vertical-align: middle;
                    /*line-height: 10em;*/

                }
                .left-list-top {
                    /*background-color: yellow;
                    background-color: rgb(180,180,255);*/
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 70%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-messageContent-top {
                    /*background-color: yellow;*/
                    /*background-color: rgb(180,180,255);*/
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 64%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-previewImage-top {
                    /*background-color: green;*/
                    /*background-color: rgb(180,180,255);*/
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 27%;
                    text-align: center;
                    vertical-align: middle;
                    border-right: thin solid rgb(127,127,255);
                }
                .rsmSelect-isActive-top {
                    /*background-color: #a52a2a;*/
                    /*background-color: rgb(180,180,255);*/
                    float: left;
                    min-height: 30px;
                    overflow: hidden;
                    width: 8%;
                    text-align: center;
                    vertical-align: middle;
                }
                #right-controller {
                    /*background-color: red;*/
                    min-height: 700px;
                    overflow: hidden;
                    width: 15%;
                    height: 25em;
                    position: relative;
                    /*line-height: 10em;*/

                }
                #left-controller {
                    /*background-color: yellow;*/
                    float: left;
                    /*min-height: 700px;*/
                    height: 750px;
                    overflow: auto;
                    width: 85%;
                    position: relative;
                }
                #rsmSelect-controller {
                    /*background-color: yellow;*/
                    float: left;
                    /*min-height: 700px;*/
                    height: 750px;
                    overflow: hidden;
                    width: 100%;
                }
                .center{margin:auto; width: 100%; height: 100%;}
                .userAction-import {
                    /*background-color: green;*/
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
                    /*background-color: red;*/
                    min-height: 100px;
                    overflow: auto;
                    width: 100%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmAttributes-import {
                    /*background-color: red;*/
                    min-height: 100px;
                    overflow: auto;
                    width: 58%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmPreview-import {
                    /*background-color: yellow;*/
                    min-height: 100px;
                    overflow: auto;
                    width: 30%;
                    float: right;
                    height: 9em;
                    /*line-height: 8em;*/
                }
                .userAction-select {
                    /*background-color: green;*/
                    min-height: 100px;
                    min-width: 10%;
                    overflow: hidden;
                    width: 10%;
                    /*height: 8em;*/
                    float: left;
                    /*line-height: 8em;*/
                    padding: 5px;
                }
                .rsmAttributes-select {
                    /*background-color: red;*/
                    min-height: 100px;
                    overflow: auto;
                    width: 53%;
                    height: 10em;
                    float: left;
                    line-height: 2em;
                }
                .rsmPreview-select {
                    /*background-color: yellow;*/
                    min-height: 100px;
                    overflow: auto;
                    width: 27%;
                    float: left;
                    height: 9em;
                    /*line-height: 8em;*/
                }
                .isActive-select {
                    /*background-color: blue;*/
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
                    /* support: IE7 */
                    *height: 1.7em;
                    *top: 0.1em;
                }
                .custom-combobox-input {
                    margin: 0;
                    padding: 0.3em;
                }
                .button {
                    min-width: 100px;
                }
                .topDiv {
                    width:100%;
                    /*background-color: rgb(240,240,255);*/
                    background-color: rgb(180,180,255);
                    /*border: thin solid rgb(127,127,255);*/
                    height: 30px;
                    /* CSS3 */
                    border-top-left-radius: 10px;
                    border-top-right-radius: 10px;
                    /* Webkit */
                    -webkit-border-top-left-radius: 10px;
                    -webkit-border-top-right-radius: 10px;
                    /* Firefox */
                    -moz-border-radius-topleft: 10px;
                    -moz-border-radius-topright: 10px;
                    /* KHTML (Konqueror) */
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
                    /*overflow-y: scroll;*/
                    scrollbar-3dlight-color:#FFFFFF;
                    scrollbar-arrow-color:#000000;
                    scrollbar-base-color:#FF9999;
                    scrollbar-darkshadow-color:#000000;
                    scrollbar-face-color:#000000;
                    scrollbar-highlight-color:#000000;
                    scrollbar-shadow-color:#0033CC;
                    height:650px;
                }
                /*
                  Hide radio button (the round disc)
                  we will use just the label to create pushbutton effect
                input[type=radio] {
                    display:none;
                }
                */

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

                /*
                  Change the look-n-feel of labels (which are adjacent to radiobuttons).
                  Add some margin, padding to label
                */
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

                /*
                 Change background color for label next to checked radio button
                 to make it look like highlighted button
                */
                input[type=radio]:checked + label {
                    background-image: none;
                    outline: 0;
                    -webkit-box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    -moz-box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
                    background-color:#669fff;
                }

                /* Begin Navigation Bar Styling */
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
                    border-right: 1px solid #ccc; }
                    /* End navigation bar styling. */

                    /* This is just styling for this specific page. */
                body {
                    background-color: #CCC;
                    font: small/1.3 Arial, Helvetica, sans-serif; }
                h1 {
                    font-size: 1.2em;
                    padding: 1em 10px;
                    color: #069;
                    /*padding-left: 85px;*/
                }
                #content {
                    padding: 0 20px 20px; }

                #leftHeader {
                    float: left;
                    width: 75px;
                    height: 100%
                }
                #rightHeader {
                    /* float: right;
                   width: 100%;*/
                    height: 75px;
                    background-color: #fff;
                    vertical-align: middle;
                    padding-left: 75px;
                    padding-top: 5px;
                }
                #rsmEdit-top-left {
                    width:52%;
                    background-color: rgb(240,240,255);
                    /*background-color: rgb(180,180,255);*/
                    border: thin solid rgb(127,127,255);
                    height: 65px;
                    text-align: center;
                    float: left;
                    padding-top: 20px;
                    /*margin-left: 20px;*/
                }
                #rsmEdit-top-right {
                    width:50%;
                    background-color: rgb(240,240,255);
                    /*background-color: rgb(180,180,255);
                    /*border: thin solid rgb(127,127,255);*/
                    height: 80px;
                    text-align: center;
                    float: right;
                    padding-top: 5px;
                    /*margin-right: 20px;*/
                }
                #rsmEdit-bottom {
                    width:100%;
                    background-color: rgb(240,240,255);
                    /*background-color: rgb(180,180,255);
                    /*border: thin solid rgb(127,127,255);*/
                    height: 80px;
                    text-align: center;
                    float: right;
                    padding-top: 5px;
                    /*margin-right: 20px;*/
                }
                #rsmEdit-left {
                    /*background-color: yellow;*/
                    width: 55%;
                    min-width: 55%;
                    float: left;
                    min-height: 750px;
                    position: relative;
                    overflow: auto;
                }
                #rsmEdit-right {
                    /*background-color: red;*/
                    width: 45%;
                    min-width: 45%;
                    float: right;
                    min-height: 750px;
                    overflow: auto;
                    /*position: relative;*/
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
                    /*background-color: yellow;*/
                    width: 55%;
                    float: left;
                    min-height: 550px;
                }
                #rsmTest-right {
                    /*background-color: red;*/
                    width: 45%;
                    /*float: right;*/
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

            </style>
    <!--
            <script>
                $(function() {
                    var icons = {
                        header: "ui-icon-circle-arrow-e",
                        activeHeader: "ui-icon-circle-arrow-s"
                    };
                    $( "#accordion" ).accordion({
                        icons: icons
                    });
                    $( ".button" ).button();
                    $( "#radioset" ).buttonset();
                    $( "#tabs" ).tabs();
                    $( "#dialog" ).dialog({
                        autoOpen: false,
                        width: 400,
                        buttons: [
                            {
                                text: "Ok",
                                click: function() {
                                    $( this ).dialog( "close" );
                                }
                            }/*,
                            {
                                text: "Cancel",
                                click: function() {
                                    $( this ).dialog( "close" );
                                }
                            }*/
                        ]
                    });
                    // Link to open the dialog
                    $( "#dialog-link" ).click(function( event ) {
                        $( "#dialog" ).dialog( "open" );
                        event.preventDefault();
                    });
                    $( "#datepicker" ).datepicker({
                        showOn: "button",
                        buttonImage: "http://dwproxy.corp.pinger.com:8082/css/images/date-picker-icon.png",
                        buttonImageOnly: true
                    });
                    $( "#slider" ).slider({
                        range: true,
                        values: [ 17, 67 ]
                    });
                    $( "#progressbar" ).progressbar({
                        value: 20
                    });
                    // Hover states on the static widgets
                    $( "#dialog-link, #icons li" ).hover(
                        function() {
                            $( this ).addClass( "ui-state-hover" );
                        },
                        function() {
                            $( this ).removeClass( "ui-state-hover" );
                        }
                    );
                });

            </script>

            <script>
                $(function() {
                    var selected
                    $( "#startDate" ).datepicker({currentText: "Now", showButtonPanel: true});
                    $( "#startDate" ).datepicker( "option", "dateFormat", "yy-mm-dd");
                    $( "#startDate" ).datepicker( "option", "dateFormat", "yy-mm-dd");
                    $( "#startDate" ).datepicker( "setDate", "2015-01-01");


                    $( "#endDate" ).datepicker({currentText: "Now",showButtonPanel: true});
                    $( "#endDate" ).datepicker( "option", "dateFormat", "yy-mm-dd");
                    $( "#endDate" ).datepicker( "option", "dateFormat", "yy-mm-dd");
                    $( "#endDate" ).datepicker( "setDate", "2016-01-01");
                });
            </script>
            -->

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

        $this->_collapsibleCSS($facilityCount);
        $this->_collapsibleJS($facilityCount);
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
}
