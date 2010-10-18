

// Code für Inedit Funktion
 // Neue Preisstaffel
 	
 	var pricemode = 0;		// Eingabemodus, 0=Brutto, 1=Netto

    
    //staffel[1] = new Array();
    //staffel[1]["von"] = 1;
    //staffel[1]["bis"] = "x";
    
function editCell(id, cellSpan, numericID, group) {
	//alert(id);
	//alert("id"+id+"cellspan"+cellSpan+"numericID"+numericID);
        var inputWidth = (document.getElementById(id).offsetWidth / 7);
        // var inputWidth = 30;
        var oldCellSpan = cellSpan.innerHTML;
		//alert("<form name=\"formularONE\" onsubmit=\"parseForm('"+id+"', '"+id+"input','"+numericID+"');return false;\" style=\"margin:0;\">");
		//alert(document.getElementById(id).innerHTML);
        
		document.getElementById(id).innerHTML = "<input type=\"text\" class=\"dynaInput\" id=\""+id+"input\" size=\""+ inputWidth + "\" onblur=\"parseForm('"+id+"', '"+id+"input','"+numericID+"','"+group+"');return false;\">";
        if(oldCellSpan=="beliebig") oldCellSpan=0;
        document.getElementById(id+"input").value = oldCellSpan;
        document.getElementById(id+"input").focus();
        document.getElementById(id).style.background = '#fff';
        document.getElementById(id).style.border = '1px solid #c3daf9';
        document.getElementById(id).style.width = '50px';
}
 
function textChanger_cb(result) {
	    	
        var result_array=result.split("~~|~~");
        document.getElementById(result_array[1]).innerHTML = "<div  style=\"cursor : hand;\" onclick=\"editCell('test1', this);\">"+result_array[0]+"</div>";
        //Fat.fade_element(result_array[1], 30, 1500, "#EEFCC5", "#FFFFFF")
}

function str_replace (subject, search, replace)
	{
		var result = "";
		var  oldi = 0;
		subject = subject+'';
		for (i = subject.indexOf (search); i > -1; i = subject.indexOf (search, i))
		{
			result += subject.substring (oldi, i);
			result += replace;
			i += search.length;
			oldi = i;
		}
		return result + subject.substring (oldi, subject.length);
	}

function parseForm(cellID, inputID, numericID, group) {
        var temp = document.getElementById(inputID).value;
        var obj = /^(\s*)([\W\w]*)(\b\s*$)/;
        if (obj.test(temp)) { temp = temp.replace(obj, '$2'); }
        var obj = /  /g;
        while (temp.match(obj)) { temp = temp.replace(obj, " "); }
        if (temp == " ") { temp = ""; }
        //if (! temp) {alert("This field must contain at least one non-whitespace character.");return;}
        if (! temp) temp = "beliebig";
        var st = document.getElementById(inputID).value + '~~|~~' + cellID;
        document.getElementById(cellID).innerHTML = "<span class=\"update\">Aktualisiere</span>";
        textChanger_cb(st);

        temp = str_replace(temp, ",", ".");
        
        if (staffel[group][numericID]["von"] > temp) temp = staffel[group][numericID]["von"];
        
        staffel[group][numericID]["bis"] = temp;
        //console.log("TEST");
        /*console.log(parseFloat(staffel[group][numericID]["bis"]+0.01));
        console.log(parseFloat(staffel[group][numericID]["bis"])+0.01);
		*/
        addPriceRange(parseFloat(staffel[group][numericID]["bis"])+0.01,"beliebig",numericID,group);
	    document.getElementById(cellID).style.border = 'none';
}
	
// Eine Preisstaffel aus der Liste entfernen
function deletePriceRange(id,group){

	staffel[group].length = staffel[group].length-1;
		
	staffel[group][staffel[group].length-1]["bis"] = "beliebig";

	generateInnerHtml();
}

// Return a boolean value telling whether // the first argument is an Array object. 

// Preisstaffel hinzufügen
function addPriceRange (von, bis, id, group) {


   //	alert("test" + staffel[0]["von"]);
   id = parseInt(id);
  // alert("ID:"+id+"#");
   	neupos = parseInt(id)+1;//staffel.length;
   //	alert(neupos);
   	//
	try {
		if (staffel[group][neupos]["von"]){
			// ... 
			//alert("already set");
			//Update this element
			//for (i=id+1;i<=staffel.length-1;i++){

			staffel[group][neupos]["von"] = parseInt(staffel[id]["bis"])+1;
				//bis = staffel[i]["bis"];
			//}
			generateInnerHtml();
		}
	} catch (error) {
			//alert(error);
			// Element doesn´t exist
			//alert(bis);
			//alert("IDTRY:"+id+"#");
			//alert(staffel[id]["bis"]);
			if (staffel[group][id]["bis"]!="beliebig"){
				//alert("!=");
				staffel[group][neupos] = new Array();
				//if (von<bis) bis = von;
	   			staffel[group][neupos]["von"] = von;
	   			staffel[group][neupos]["bis"] = bis;
	   			
	   			
	   			
			}
			generateInnerHtml();
	}

   	//}
}

// HTML-Code erzeugen
function generateInnerHtml () {
	
	


	// Anzahl der Elemente im Array feststellen
		
		//alert(anzahlStaffeln);
		
		// Template für die einzelnen Staffel-Zeilen
	  	
	// ==============================================
   	// Für jede Preisgruppe ...
   	// ==============================================
   	for (p=0;p<=pricegroups.length-1;p++){
   		var group = pricegroups[p];
   		
   		if (defaultmwst[p]==0){
   			//mwstselect = "<select  name=\"tax["+group+"]\" id=\"tax"+group+"\" class=\"choose\"><option value=\"brutto\">Brutto</option><option value=\"netto\" selected>Netto</option></select>";
   		}else {
   			//mwstselect = "<select  name=\"tax["+group+"]\" id=\"tax"+group+"\" class=\"choose\"><option value=\"brutto\" selected>Brutto</option><option value=\"netto\">Netto</option></select>";
   		}
   		var mwstselect = "";
   		var template  = "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"180\"><strong>Versandkosten nach Gewicht (KG)</strong></td><td width=\"35\">"+mwstselect+"</td><td width=50><strong> * FAKTOR +</strong></td><td width=\"7%\"></td><td width=\"70\"><strong>Versandkosten (€)</strong></td><td width=\"5\">&nbsp;</td></tr>";
   		
   		anzahlStaffeln = staffel[group].length;
		for (i=0;i<=anzahlStaffeln-1;i++){
			var pricebrutto;
			var pricepseudo;
			
			von = staffel[group][i]["von"];
			bis = staffel[group][i]["bis"];
			
			// Preise aus Textfeldern ziehen und HTML-Part neu generieren
			try
			{
			pricebrutto = document.getElementById("priceregulary"+group+i).value;
			pricepseudo = document.getElementById("pricepseudo"+group+i).value;
			priceek = document.getElementById("priceEK"+group+i).value;
			} catch (error){
				//alert(error);	
				try 
				{
					if (staffel[group][i]["pricevk"]){
						pricebrutto = staffel[group][i]["pricevk"];
					}else {
						pricebrutto = 0;
					}
					if (staffel[group][i]["pricefactor"]){
						pricefactor = staffel[group][i]["pricefactor"];
					}else {
						pricefactor = 0;
					}
					if (staffel[group][i]["pricepseudo"]){
						pricepseudo = staffel[group][i]["pricepseudo"];
					}else {
						pricepseudo = 0;
					}
					
					if (staffel[group][i]["priceek"]){
						priceek = staffel[group][i]["priceek"];
					}else {
						priceek = 0;
					}

				} catch (error){
					pricebrutto = 0;
					pricepseudo = 0;
					priceek = 0;
					pricefactor = 0;
				}
			}
			
			// Bei letzter Zeile Löschmöglichkeit einblenden
			if ((i==(anzahlStaffeln-1)) && (anzahlStaffeln>1)){
				//alert ("ALALRRM");
				del = "<a class=\"ico delete\" onclick=\"deletePriceRange("+i+", '"+group+"')\"></a>";
			}else {
				del = "";	
			}
			//alert(del);
			template = template + "<tr><td><table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\"><tr><input type=\"hidden\" name=\"von["+group+"]["+i+"]\" value=\""+ von +"\"><input type=\"hidden\" name=\"bis["+group+"]["+i+"]\" value=\""+ bis +"\"><td width=\"20\">" + von + "</td><td width=\"25\">bis</td><td style=\"cursor : pointer; cursor : hand;\" width=\"60\" id=\""+group+"row"+i+"\"><div style=\"border:2px solid #c3daf9;width:50px;height:15px;background-color:#FFF\" onclick=\"editCell('"+group+"row"+i+"', this,"+i+",'"+group+"');\">"+bis+"</div></td></tr></table></td><td width=\"35\"></td><td width=50><input autocomplete=\"off\" class=\"textbox\"  onKeyPress=\"return goodchars(event,'0123456789,.')\"  id=\"pricefactor"+group+""+i+"\" name=\"pricefactor["+group+"]["+i+"]\" type=\"text\" size=\"7\" value=\""+pricefactor+"\" /></td><td>"+del+"</td><td><input autocomplete=\"off\" class=\"textbox\"  onKeyPress=\"return goodchars(event,'0123456789,.')\"  id=\"priceregulary"+group+""+i+"\" name=\"priceregulary["+group+"]["+i+"]\" type=\"text\" size=\"7\" value=\""+pricebrutto+"\" /></td><td>&nbsp;</td></tr>";		
		} // Für jede Staffel
		
		template = template + "</table>";

		//alert("test");
		
		try {
			document.getElementById("pricetemplate"+group).innerHTML = template;	
		} catch (el) {}
		
		//pricetemplate
   	} // Für jede Preisgruppe
		
}
// Brutto/Netto Modechanger
function changeMode (idElement)
{
	
}
// Proof values
function checkKey (key)
{
	
}

// User function
function getkey(e)
{
if (window.event)
   return window.event.keyCode;
else if (e)
   return e.which;
else
   return null;
}
// Allow spezific keys
function goodchars(e, goods)
{
//alert ("Event"+getkey(ef)+"goods:"+goods);
var key, keychar;

key = getkey(e);
//alert(key);
if (key == null) return true;

// get character
keychar = String.fromCharCode(key);
keychar = keychar.toLowerCase();

// Change Mode
if (key==44 || key==46){ 
	key=46;
	return true;
}

if (keychar=="c") {
	if (pricemode){
		pricemode = 0;	
	}else {
		pricemode = 1;	
	}
	
	return false;
}

goods = goods.toLowerCase();

// check goodkeys
if (goods.indexOf(keychar) != -1)
	return true;

// control keys

if ( key==null || key==0 || key==8 || key==9 || key==13 || key==27 )
   return true;

// else return false
return false;
}
