jQuery(document).ready(function($) {
		$(document).ready(function() {
			$('#btnAdd').click(function() {
                            console.log('Click Add');
				var num		= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				var newNum	= new Number(num + 1);		// the numeric ID of the new input field being added

				// create the new element via clone(), and manipulate it's ID using newNum value
				var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);
				
				// manipulate the name/id values of the input inside the new element
				newElem.find('.first_name').attr('id', 'first_name' + newNum).val('');
				newElem.find('.last_name').attr('id', 'last_name' + newNum).val('');
				newElem.find('.mobile').attr('id', 'mobile' + newNum).val('');
				newElem.find('.email').attr('id', 'email' + newNum).val('');
				newElem.find('.male').attr('id', 'male' + newNum).val('');
                                newElem.find('.female').attr('id', 'female' + newNum).val('');
                                newElem.find('select').attr('id', 'people_type_id' + newNum).val('');
				// insert the new element after the last "duplicatable" input field
				$('#input' + num).after(newElem);
				
				
				// enable the "remove" button
				$('#btnDel').attr('disabled','');

				// business rule: you can only add 50 names
				if (newNum == 50)
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


