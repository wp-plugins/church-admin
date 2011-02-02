<?php
//Set up header for admin
add_action('admin_head', 'church_admin_header');
function church_admin_header()
{
echo'<style type="text/css">
.red{border:1px solid #f00!important;}
form li {width:700px;}
label{width:200px; float:left;font-weight:bold;}
input[type="text"]{width:300px;}
.note{display:inline-block;height: 100%;}
#church_admin_phone
    {
        background:url("'.CHURCH_ADMIN_URL.'images/mobilephone.png") no-repeat;
        width:156px;
        height:295px;
        font-size:10pt;
    }
    
    #church_admin_whoto {position:relative; top:55px;left:10px;width:130px;}

#church_admin_message{position:relative;top:60px;left:10px; }
#church_admin_message textarea{width:135px; height:100px;background:transparent;border:1px solid orange;}
#church_admin_number {position:relative;top:35px;left:10px;}
#church_admin_number input{width:135px;background:transparent;border:1px solid orange;}
#church_admin_submit{position:relative;top:95px;left:20px;}  
.farbtastic {
  position: relative;
  top:0px;left:200px;
  border:1px solid grey;
}
.farbtastic * {
  position: absolute;
  cursor: crosshair;
}
.farbtastic, .farbtastic .wheel {
  width: 195px;
  height: 195px;
}
.farbtastic .color, .farbtastic .overlay {
  top: 47px;
  left: 47px;
  width: 101px;
  height: 101px;
}
.farbtastic .wheel {
  background: url('.CHURCH_ADMIN_INCLUDE_URL.'wheel.png) no-repeat;
  width: 195px;
  height: 195px;
}
.farbtastic .overlay {
  background: url('.CHURCH_ADMIN_INCLUDE_URL.'mask.png) no-repeat;
}
.farbtastic .marker {
  width: 17px;
  height: 17px;
  margin: -8px 0 0 -8px;
  overflow: hidden; 
  background: url('.CHURCH_ADMIN_INCLUDE_URL.'marker.png) no-repeat;
}

</style>
<script type="text/javascript">
function toggle(obj){
	var div1 = document.getElementById(obj)
	if (div1.style.display == \'none\') {
		div1.style.display = \'block\'
	} else {
		div1.style.display = \'none\'
	}
};
</script>
';    
}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{echo'<style type="text/css">
.calendar-date-switcher {
        height:25px;
        text-align:center;
        border:1px solid #D6DED5;
        background-color:#E4EBE3;
     }
     .calendar-date-switcher form {
        margin:0;
        padding:0;
     }
     .calendar-date-switcher input {
        border:1px #D6DED5 solid;
     }
     .calendar-date-switcher select {
        border:1px #D6DED5 solid;
     }
    .calendar-heading {
        height:25px;
        text-align:center;
        border:1px solid #D6DED5;
        background-color:#E4EBE3;
     }
     .calendar-next {
        width:25%;
        text-align:center;
     }
     .calendar-prev {
        width:25%;
        text-align:center;
     } 
    .tooltip{
   position: absolute;
   padding: 10px 13px;
   z-index: 2;
   
   color: #303030;
   background-color: #f5f5b5;
   border: 1px solid #DECA7E;
   
   font-family: sans-serif;
   font-size: 12px;
   line-height: 18px;
   text-align: center;
}     
/*Post it note*/
.Postit{font-size:1.2em !important;width: 250px;background-color: #FFFFB3;border-right: 3px solid #BBBBBB;border-bottom: 3px solid #BBBBBB;border-left: 1px solid #DDDDDD;border-top: 1px solid #DDDDDD;color: #66666;padding: 5px;margin: 5px;}
.Postit h1,.Postit h2{padding:0 0px;text-transform:uppercase;font-size: 1.2em;}
.Postit ul{margin:0;font-size:0.75em;list-style:none;padding:0em;}
.Postit a {text-indent:0;padding:0;}
.Postit li {text-indent:0;padding:0 0 10px 0;margin:0!important;list-style:none;border-bottom:none !important;}
</style>
     ';

    
}
?>