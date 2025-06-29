
	function submitForm(action) {
		document.getElementById('form1').action = action;
		document.getElementById('form1').submit();
	}

	/*
	 *    submit for when the enter key is pressed (code 13)
	 */
	function pressed(e) {
		// Has the enter key been pressed?
		if ( (window.event ? event.keyCode : e.which) === 13) {
			// If it has been so, manually submit the <form>
			document.forms[0].submit();
		}
	}

	$(function() {
		$( "#datepicker, #datepicker2" ).datepicker();
	});

   /*
	*	This displays the time
	*/
    function updateTime() {
        let now = new Date();
        let hours   = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        if (minutes < 10){
            minutes = "0" + minutes;
        }
        if (seconds < 10){
            seconds = "0" + seconds;
        }
		let hrs = hours;
		if(hours > 12){
			hrs -= 12;
		}
		if(hours === 0){
			hrs = 12;
		}
        let v = hrs + ":" + minutes; // + ":" + seconds;
		v += (hours > 11) ? " pm " : " am ";
        document.getElementById('time').innerHTML=v;
		let dayOfMonth = now.getDate();
		if (dayOfMonth < 10) { dayOfMonth = "0" + dayOfMonth; }
        let theDate = now.getMonth()+1 + "/" + dayOfMonth + "/" + now.getFullYear();
		let weekDays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
		console.log(v + " the time ");
		console.log(theDate + " today's date " + weekDays[now.getDay()]);
		console.log(now.getTime() + " milliseconds since 1/1/1970");


    }
	// This calls the function now and then every 20 seconds, onLoad
	function showClock() {
		updateTime();
		setInterval("updateTime()",20000);
	}

   /*
	*	Fade div's
	*/
	setTimeout(function() {
	    $('#showup').delay(5000).slideUp( 2000 );
	}, 1000); // <-- time in milliseconds

	setTimeout(function() {
	    $('#showupinq').delay(50000).slideUp( 2000 );
	}, 1000); // <-- time in milliseconds

   /*
	*	Show and hide any div
	*/
	function showHide(id) {
		let el = document.getElementById(id);
		if( el && el.style.display == 'block')    
			el.style.display = 'none';
		else 
			el.style.display = 'block';
	}
	function showHide2(id,newdisplay) {
		let el = document.getElementById(id);
		if( el && el.style.display == 'block')    
			el.style.display = 'block';
		else 
			el.style.display = 'none';
	}
	function showid(id) {
		let el = document.getElementById(id);
		el.style.display = 'block';
		alert("show");
	}
	function hideid(id) {
		let el = document.getElementById(id);
		el.style.display = 'none';
		alert("hide");
	}
	function swap(on, off) {
		document.getElementById(on).style.display = 'block';
		document.getElementById(off).style.display = 'none';
	}


	/*
	 *    <input onkeypress="return numbersOnly(event);" name="VAT_Number">
	 *    only allow numeric input
	 */
	function numbersOnly(e) {
		let unicode=e.charCode? e.charCode : e.keyCode;
		if  (unicode!==8 && unicode!==9) { //if the key isn't the backspace key or TAB (which we should allow)
			if (unicode<48||unicode>57) return false;//if not a number return false //disable key press
		}
	} 


	/*
	 *    setup listeners on class blue, pink and admin
	 */
	function buttonSetup (button) {

		function makeItHappenDown(x,buttonDown) {
			return function(){
				x.className=buttonDown;
			}
		}
		function makeItHappenUp(x,buttonUp) {
			return function(){
				x.className=buttonUp;
			}
		}

		if (document.getElementsByClassName(button+"_up")) {
			let a = document.getElementsByClassName(button+"_up");
			let x;
			for (let i = 0; i < a.length; ++i) {
				x = a[i];
				x.addEventListener("mousedown", makeItHappenDown(x,button+"_down"), false);
				x.addEventListener("mouseup", makeItHappenUp(x,button+"_up"), false);
			}
		}
	}

    // handle the form submit event
    function prepareEventHandlers() {
        document.getElementById("frmContact").onclick = function() {
            let result = false;
            // prevent a form from submitting if no email.
            if (document.getElementById("mandatoryUsername").value === "") {
                document.getElementById("errorUsername").innerHTML = "Username is mandatory";
                // to STOP the form from submitting
                result = false;
            } else {
                // reset and allow the form to submit
                document.getElementById("errorUsername").innerHTML = "";
                result = true;
            }
            if (document.getElementById("mandatoryPassword").value === "") {
                document.getElementById("errorPassword").innerHTML = "Password is mandatory";
                result = false;
            } else {
                document.getElementById("errorPassword").innerHTML = "";
                result = true;
            }

            return result;
        };
    }

	window.onload = function () {

		// if there is an element with id time start the clock
		if(document.getElementById("time")) {
			showClock();
		}

		//	document.forms['form1'].elements['barcode'].focus(); 
		if (document.getElementById('focus')) {
			document.getElementById('focus').focus();
		}

		buttonSetup("del");
		buttonSetup("edit");
		buttonSetup("blue");
		buttonSetup("pink");
		buttonSetup("admin");

        prepareEventHandlers();

	}
