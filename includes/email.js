jQuery(document).ready(function($) {
$(document).ready(function() {
        $("input[name=type]:radio").click(function() { // attack a click event on all radio buttons with name 'type'
                if($(this).val() == 'everyone') {//check which radio button is clicked 
                        //do something
			document.getElementById('smallgroup').style.display ='none';
			document.getElementById('individuals').style.display ='none';
                } else if($(this).val() == 'individuals') {
                        //do something else

			document.getElementById('individuals').style.display ='block';
			document.getElementById('smallgroup').style.display ='none';
                } else if($(this).val() == 'smallgroup') {
                        //do something else again
			document.getElementById('individuals').style.display ='none';
			document.getElementById('smallgroup').style.display ='block';
                }
		
        });
});


$(document).ready(function() {
			$('#btnAdd').click(function() {
				var num		= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				var newNum	= new Number(num + 1);		// the numeric ID of the new input field being added

				// create the new element via clone(), and manipulate it's ID using newNum value
				var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);
				
				// manipulate the name/id values of the input inside the new element
				
				newElem.find('select').attr('id', 'person' + newNum).val('');
				
				
				// insert the new element after the last "duplicatable" input field
				$('#input' + num).after(newElem);
				 $('#hide' + newNum).hide();
				
				// enable the "remove" button
				$('#btnDel').attr('disabled','');

				// business rule: you can only add 5 names
				if (newNum == 25)
					$('#btnAdd').attr('disabled','disabled');
			});

			$('#btnDel').click(function() {
				var num	= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				$('#input' + num).remove();		// remove the last element

				// enable the "add" button
				$('#btnAdd').attr('disabled','');

				// if only one element remains, disable the "remove" button
				if (num-1 == 1)
					$('#btnDel').attr('disabled','disabled');
			});

			$('#btnDel').attr('disabled','disabled');
		});




});