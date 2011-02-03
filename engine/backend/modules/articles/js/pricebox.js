var pricemode = 0;		// Eingabemodus, 0=Brutto, 1=Netto

    
function editCell(id, cellSpan, numericID, group) {

        var inputWidth = ($(id).offsetWidth / 7);
       
        var oldCellSpan = cellSpan.innerHTML;
		
        
	
;
		$(id).innerHTML = "<input type=\"text\" class=\"dynaInput\" id=\""+id+"input\" size=\""+ inputWidth + "\" onblur=\"parseForm('"+id+"', '"+id+"input','"+numericID+"','"+group+"');return false;\">";
        
       $(id+"input").value = oldCellSpan;
       $(id+"input").focus();
       $(id).style.background = '#fff';
       $(id).style.border = '1px solid #c3daf9';
       $(id).style.width = '50px';
}
 
function textChanger_cb(result) {
	    	
        var result_array=result.split("~~|~~");
        $(result_array[1]).innerHTML = "<div  style=\"cursor : hand;\" onclick=\"editCell('test1', this);\">"+result_array[0]+"</div>";
}

function parseForm(cellID, inputID, numericID, group) {
        
	var temp = $(inputID).value;
        
        var obj = /^(\s*)([\W\w]*)(\b\s*$)/;
        
        if (obj.test(temp)) { 
        	temp = temp.replace(obj, '$2'); 
        }
        
        var obj = /  /g;
        
        while (temp.match(obj)) { temp = temp.replace(obj, " "); }
        
        
        if (temp == " ") { temp = ""; }
        
        if (! temp) {alert("This field must contain at least one non-whitespace character.");return;}
        
        var st = $(inputID).value + '~~|~~' + cellID;
        
        $(cellID).innerHTML = "<span class=\"update\">Aktualisiere</span>";
        
        textChanger_cb(st);
       
        
        if (staffel[group][numericID]["von"] > temp) temp = staffel[group][numericID]["von"];
        
        staffel[group][numericID]["bis"] = temp;
        
		addPriceRange(parseInt(staffel[group][numericID]["bis"])+1,"beliebig",numericID,group);

        $(cellID).style.border = 'none';
}
	
// Eine Preisstaffel aus der Liste entfernen
function deletePriceRange(id,group){

	staffel[group].length = staffel[group].length-1;
		
	staffel[group][staffel[group].length-1]["bis"] = "beliebig";

	generateInnerHtml();
}


// Preisstaffel hinzufügen
function addPriceRange (von, bis, id, group) {


  
   id = parseInt(id);
 
   	neupos = parseInt(id)+1;
  
 	$('mainPrices').setStyle('height','100%');
	try {
		if (staffel[group][neupos]["von"]){
		
			staffel[group][neupos]["von"] = parseInt(staffel[id]["bis"])+1;
			generateInnerHtml();
		}
	} catch (error) {
		if (staffel[group][id]["bis"]!="beliebig"){
			staffel[group][neupos] = new Array();
   			staffel[group][neupos]["von"] = von;
   			staffel[group][neupos]["bis"] = bis;
		}
		generateInnerHtml();
	}

 
}

// HTML-Code erzeugen
function generateInnerHtml () {
	// ==============================================
   	// Für jede Preisgruppe ...
   	// ==============================================
   	for (p=0;p<=pricegroups.length-1;p++){
   		var group = pricegroups[p];
   		
   
   		var template  = $('priceHeader').innerHTML;
   		
   		anzahlStaffeln = staffel[group].length;
   		
		for (i=0;i<=anzahlStaffeln-1;i++){
			var pricebrutto;
			var pricepseudo;
			
			von = staffel[group][i]["von"];
			
			if (!i){
				bis = staffel[group][i]["bis"];
			}else if (i == anzahlStaffeln -1) {
				bis = "beliebig";
			}else {
				bis = staffel[group][i]["bis"];
				if (bis=="beliebig"){
					// Should fix ...
				}
				/*if (parseFloat(bis)!=parseFloat(staffel[group][i+1]["von"])-1){
					bis = parseFloat(staffel[group][i-1]["bis"])+1;
				}*/
			}
			
			
			// Preise aus Textfeldern ziehen und HTML-Part neu generieren
			try
			{
				pricebrutto = $("priceregulary"+group+i).value;
				pricepseudo = $("pricepseudo"+group+i).value;
				priceek = $("priceEK"+group+i).value;
				try {
					percentValue =  $("percent"+group+i).value;
				} catch (error){}
			} catch (error){
				
				try 
				{
					if (staffel[group][i]["pricevk"]){
						pricebrutto = staffel[group][i]["pricevk"];
					}else {
						pricebrutto = 0;
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
					
					if (staffel[group][i]["percent"]){
						percentValue = staffel[group][i]["percent"];
					}else {
						percentValue = 0;
					}

				} catch (error){
					
					pricebrutto = 0;
					pricepseudo = 0;
					priceek = 0;
					percentValue = 0;
				}
			}
			
			// Bei letzter Zeile Löschmöglichkeit einblenden
			if ((i==(anzahlStaffeln-1)) && (anzahlStaffeln>1)){
				del = "<a class=\"ico delete\" style=\"margin-top:8px\" onClick=\"deletePriceRange("+i+", '"+group+"')\"></a>";
			}else {
				del = "";	
			}
			
			
			
			// Hide percent-input in first row
			if (!i){
				var enterByPercent ="";
				var enterByValue = "<input autocomplete=\"off\" class=\"textbox\"  onKeyPress=\"return validateInput(event,'0123456789,.',this,false)\"  id=\"priceregulary"+group+""+i+"\" style=\"margin: 5px 29px 0 0;font-size:10px;height:18px;\" group=\""+group+"\" onChange=\"triggerInputParent(this,'"+group+"')\" name=\"priceregulary["+group+"]["+i+"]\" type=\"text\" size=\"7\" value=\""+pricebrutto+"\" />";
			}else if (i==1) {
				// Activate trigger
				var enterByPercent = "<input  autocomplete=\"off\" group=\""+group+"\" class=\"textbox "+group+" percent\"  onKeyPress=\"return validateInput(event,'0123456789,.',this,true)\"  style=\"margin: 5px 0 0 0;font-size:10px;height:18px;\" onChange=\"triggerInput(this,"+i+")\" id=\"percent"+group+""+i+"\" name=\"percent["+group+"]["+i+"]\" type=\"text\" row=\""+i+"\" size=\"7\" value=\""+percentValue+"\" />";
				var enterByValue = "<input  autocomplete=\"off\" group=\""+group+"\" class=\"textbox "+group+" value\"  onKeyPress=\"return validateInput(event,'0123456789,.', this, true)\"  style=\"margin: 5px 29px 0 0;font-size:10px;height:18px;\" onChange=\"triggerInput(this,"+i+")\" id=\"priceregulary"+group+""+i+"\" name=\"priceregulary["+group+"]["+i+"]\" row=\""+i+"\" type=\"text\" size=\"7\" value=\""+pricebrutto+"\" />";
				
			}else {
				var enterByPercent = "<input  autocomplete=\"off\" group=\""+group+"\" class=\"textbox "+group+" percent\"  onKeyPress=\"return validateInput(event,'0123456789,.',this,true)\" style=\"margin: 5px 0 0 0;font-size:10px;height:18px;\" onChange=\"triggerInput(this,"+i+")\" id=\"percent"+group+""+i+"\" name=\"percent["+group+"]["+i+"]\" type=\"text\" row=\""+i+"\" size=\"7\" value=\""+percentValue+"\" />";
				var enterByValue = "<input  autocomplete=\"off\" group=\""+group+"\" class=\"textbox "+group+" value\"  onKeyPress=\"return validateInput(event,'0123456789,.',this,true)\" style=\"margin: 5px 29px 0 0;font-size:10px;height:18px;\" onChange=\"triggerInput(this,"+i+")\"  id=\"priceregulary"+group+""+i+"\" name=\"priceregulary["+group+"]["+i+"]\" row=\""+i+"\" type=\"text\" size=\"7\" value=\""+pricebrutto+"\" />";
			}
			
			
			
			template = template + "<div class=\"price_line\"><input type=\"hidden\" name=\"von["+group+"]["+i+"]\" value=\""+ von +"\"><input type=\"hidden\" name=\"bis["+group+"]["+i+"]\" value=\""+ bis +"\"><div class=\"fauxpricecol\"><div class=\"p_col1\"><p style=\"float: left;margin-top:7px; margin-right:5px\">" + von + " bis </p><!-- Beginn Dyn.Feld--><div style=\"cursor: pointer; width: 60px; margin: 5px 0 0 10px; float: left;\" id=\""+group+"row"+i+"\"><div style=\"border: 2px solid rgb(195, 218, 249); width: 50px; padding:3px; height: 15px; background-color: #fff; float: left;\" onclick=\"editCell('"+group+"row"+i+"', this,"+i+",'"+group+"');\">"+bis+"</div></div><div class=\"fixfloat\"></div><!-- Ende Dyn Feld-->" + del + "</div><!-- VK START--><div class=\"p_col2\">"+ enterByValue + enterByPercent+"</div><!-- VK Ende --><!-- Pseudo VK START --><div class=\"p_col3\"><input autocomplete=\"off\" id=\"pricepseudo"+group+""+i+"\" class=\"textbox\" name=\"pricepseudo["+group+"]["+i+"]\" type=\"text\" size=\"7\" value=\""+pricepseudo+"\" /></div><!-- Pseudo VK ENDE--><!-- EK START --><div class=\"p_col4\"><input autocomplete=\"off\" id=\"priceEK"+group+""+i+"\" class=\"textbox\" name=\"priceEK["+group+"]["+i+"]\" type=\"text\" size=\"7\" value=\""+priceek+"\" /></div><!-- EK ENDE --><div class=\"fixfloat\"></div></div></div>";
			
		
		} // Für jede Staffel
		
		$("pricetemplate"+group).innerHTML = template;
		
   	}// Für jede Preisgruppe
		
}

// Parent wurde verändert
function triggerInputParent(element, group){
	// Schleife durch alle Prozentfelder und neu Berechnung des VKs
	// 2.do) Konfigurierbar ob Prozent oder Wert als Grundlage
	 var priceGroup = $('pricetemplate'+group);
	 priceGroup.getElements('input').forEach(function(el){
	 	// Percent to Value
	 	if (el.hasClass('textbox ' + group + ' percent')){
	 		// Get row
	 		var row = el.getProperty('row');
	 		
	 		var percentValue = el.value;
	 		
	 		var parentValue = element.value;
	 		
	 		parentValue = parentValue.replace(",",".");

			parentValue = parseFloat(parentValue);
	
	 		var newValue = parentValue - (parentValue/100*percentValue);
	 		
	 		// Setting new value
	 		
	 		$("priceregulary"+group+row).setProperty('value',newValue.toFixed(2));
	 		
	 		// Calculating new price for this row
	 		
	 		
	 		
	 		// ----------------------------------
	 	}
	 	// Value to Percent
	 	// 2.do
	 });
}

function triggerInput(element,row){
	
	var elementsValue = element.value;
	
	elementsValue = elementsValue.replace(",",".");
	
	element.value = elementsValue;
	elementsValue = parseFloat(elementsValue);
	if (elementsValue){
		
		if ($(element).hasClass('textbox '+$(element).getProperty('group')+' percent')){
			if (elementsValue>=100){
				elementsValue = 99;
				element.value = 99;
			}
			// by Percent
			// Get parents value
			var parent = $('priceregulary'+$(element).getProperty('group')+'0');
			
			
	 		
	 		//parent.value = parent.value.replace(",",".");

			//parent.value = parseFloat(parent.value);
			
			if (parent.value){
				// Calculating value
				var newValue = parent.value - (parent.value/100*elementsValue);
			
				$("priceregulary"+$(element).getProperty('group')+row).setProperty('value',newValue.toFixed(2)); 
				
			}
			
		}else {
		//	console.log("HERE2");
			// by Value
			var parent = $('priceregulary'+$(element).getProperty('group')+'0');

			parent.value = parent.value.replace(",",".");
			parent.value = parseFloat(parent.value);
			
			if (parent.value<=elementsValue){
		
				elementsValue = parent.value-0.01;
				element.value = parent.value-0.01;
			}
			
			if (parent.value){
				var newValue = 100 - (elementsValue / parent.value * 100); //parent.value - (parent.value/100*elementsValue);
			
				$("percent"+$(element).getProperty('group')+row).setProperty('value',newValue.toFixed(2)); 
					
			}
			
		}
	}
	
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
function validateInput (e, goods, element, triggerAction)
{

	
	
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
