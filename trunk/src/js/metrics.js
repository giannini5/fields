/*
	basic javascript used in metrics
*/

// swap hide/display on 2 divs
function toggleDisplay(id1,id2) {
	// if id1 is hidden, then show it and hide id2. else do the opposite.
	if (document.getElementById(id1).style.display == 'none') {
		document.getElementById(id1).style.display = 'block';
		document.getElementById(id2).style.display = 'none';
	} else {
		document.getElementById(id1).style.display = 'none';
		document.getElementById(id2).style.display = 'block';
	}
	return false;
}

// hide a div
function toggleDiv(divId) {
	if (document.getElementById(divId).style.display == 'none') {
		document.getElementById(divId).style.display = 'block';
	} else {
		document.getElementById(divId).style.display = 'none';
	}
	return false;
}

// check/uncheck a series of check boxes
function submitter(formName, fieldName){

  if (document.images) {
  for (i=0;i<formName.length;i++) {
    var tempobj=formName.elements[i];
    if (tempobj.name.substring(0,7)==fieldName) {

      if(formName.checkAll.checked==false)
        tempobj.checked=false;
      else
        tempobj.checked=true;
                                         
      }
    }
  }
}

function checkAllBoxes()
{
	var checkAll = document.getElementById('checkAll');
	var appCheckBoxes = document.getElementsByClassName('appCheckBox');
	if(checkAll.checked) {
		for (var i = 0; i<appCheckBoxes.length; i++) {
			appCheckBoxes[i].checked=true;
		}
	} else {
		for (var i = 0; i<appCheckBoxes.length; i++) {
			appCheckBoxes[i].checked=false;
		}

	}
       
}


