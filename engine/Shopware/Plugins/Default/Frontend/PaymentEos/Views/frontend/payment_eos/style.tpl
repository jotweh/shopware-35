@import url("{link file='frontend/_resources/styles/framework.css'}");

input[type="submit"] {
    -moz-user-select: none;
    border-radius: 3px 3px 3px 3px;
    box-shadow: 1px 1px 2px #C0C0C0;
    position: relative;
    background: url("{link file='frontend/_resources/images/buttons/button_right-large.png'}") repeat-x scroll right center transparent;
    border: 0 none;
    color: #FFFFFF !important;
    cursor: pointer;
    display: inline-block;
    font: 14px/40px Arial,sans-serif;
    height: 40px;
    margin: 0 5px 5px 0;
    padding: 0 50px 0 35px;
    text-decoration: none;
    float: right;
    margin-bottom: 1.25em;
}

input#abbrechen {
	background: url("{link file='frontend/_resources/images/buttons/button_left-large.png'}") repeat-x scroll left center transparent;
    padding: 0 35px 0 50px;
    float: left;
}

#summe, #sum_01, #sum_02 {
	font-size: 14px;
}

#karteninfo br, #area_2 br {
	display: none;
}

#karteninfo, #area_2 {
	font-weight: bold;
	line-height: 2.5em;
}

input[type="text"], select {
    left: 250px;
    position: absolute;
    margin: 0;
    font-size: 1.2em;
    margin-bottom: 1.25em;
    padding: 5px;
    width: 300px;
    font-weight: normal;
}

select {
    width: 153px;
}

select#year {
    left: 409px;
}

html {
	background: none repeat scroll 0 0 #fff;
	font: 11px/1.3em Arial,"Helvetica Neue",Helvetica,sans-serif;
	padding: 15px 15px 0 15px;
}

td.errormsg {
    width: 360px;
}

input.error, select.error {
    background-color: #FBE3E4;
    border-color: #FBC2C4;
    color: #8A1F11;
}

#area_3 input[type="text"], #area_3 select {
    left: 250px;
    position: inherit;
}
#sum_01, #area_3 div {
	float: left;
	padding-right: 10px;
}
#sum_02 {
	float: right;
	padding-right: 10px;
}
#accowner, #accnumber, #bankcode {
	clear:both;
	width: 220px;
}
#area_3 .sub_text_accept  {
	clear:both;
	float: right;
	padding-right: 0px;
}
#area_3 {
	clear: both;
    padding-top: 1.4em;
}