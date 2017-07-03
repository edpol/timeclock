/*
	jQuery(
		function() {
			jQuery('#test1').datepicker();
  
			jQuery('#test2').datepicker(  {
					onSelect: function () { $(this).change(); }
				}
			);
  
			jQuery("input").on("change", 
				function(){ console.log("change detected"); }
			);
		}
	);
*/

	function submitForm(action) {
		document.getElementById('form1').action = action;
		document.getElementById('form1').submit();
	}

	/*
	 *    submit for when the enter key is pressed (code 13)
	 */
	function pressed(e) {
		// Has the enter key been pressed?
		if ( (window.event ? event.keyCode : e.which) == 13) { 
			// If it has been so, manually submit the <form>
			document.forms[0].submit();
		}
	}

	$(function() {
		$( "#datepicker, #datepicker2" ).datepicker();
	});

/* 
	top.consoleRef = new Object();
	top.consoleRef.closed = true;

	function writeConsole(content) {
		if (consoleRef.closed)
			top.consoleRef=window.open('blank.htm','myconsole', 'width=350,height=450'+',menubar=0'+',toolbar=1'+',status=0'+',scrollbars=1'+',resizable=1')
		else 
			top.consoleRef.document.open("text/html","replace");
			//use "replace" to prevent back/forward history
			//top.consoleRef.document.open("text/html","replace");
			top.consoleRef.document.writeln(
				'<html><head><title>Console</title></head>'
				+'<body bgcolor=black onLoad="self.focus()" style="color:white;">'
				+'<pre>'+content+'</pre>'
				+'</body></html>'
			)
		top.consoleRef.document.close()
		setTimeout(function(){ top.consoleRef.close() }, 90000);
	}
*/

   /*
	*	This diplays the time 
	*/
    function updateTime() {
        var now = new Date();
        var hours   = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        if (minutes < 10){
            minutes = "0" + minutes;
        }
        if (seconds < 10){
            seconds = "0" + seconds;
        }
		hrs = hours;
        if(hours > 12){
            hrs -= 12;
        }
        var v = hrs + ":" + minutes; // + ":" + seconds;
        if(hours > 11){
            v+=" pm ";
        } else {
            v+=" am "
        }
        document.getElementById('time').innerHTML=v;

		var dayOfMonth = now.getDate();
		if (dayOfMonth < 10) { dayOfMonth = "0" + dayOfMonth; }
        var theDate = now.getMonth()+1 + "/" + dayOfMonth + "/" + now.getFullYear();
		var weekDays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
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
		var el = document.getElementById(id);
		if( el && el.style.display == 'block')    
			el.style.display = 'none';
		else 
			el.style.display = 'block';
	}
	function showHide2(id,newdisplay) {
		var el = document.getElementById(id);
		if( el && el.style.display == 'block')    
			el.style.display = 'block';
		else 
			el.style.display = 'none';
	}
	function showid(id) {
		var el = document.getElementById(id);
		el.style.display = 'block';
		alert("show");
	}
	function hideid(id) {
		var el = document.getElementById(id);
		el.style.display = 'none';
		alert("hide");
	}
	function swap(on, off) {
		document.getElementById(on).style.display = 'block';
		document.getElementById(off).style.display = 'none';
	}


	/*
	 *    <input onkeypress="return numbersonly(event);" name="VAT_Number">
	 *    only allow numeric input
	 */
	function numbersonly(e) {
		var unicode=e.charCode? e.charCode : e.keyCode;
		if  (unicode!=8 && unicode!=9) { //if the key isn't the backspace key or TAB (which we should allow)
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
			var a = document.getElementsByClassName(button+"_up");
			var x;
			for (var i = 0; i < a.length; ++i) {
				x = a[i];
				x.addEventListener("mousedown", makeItHappenDown(x,button+"_down"), false);
				x.addEventListener("mouseup", makeItHappenUp(x,button+"_up"), false);
			}
		}
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

		buttonSetup("blue");
		buttonSetup("pink");
		buttonSetup("admin");
	}
