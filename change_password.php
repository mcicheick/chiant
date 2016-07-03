	<?php
	session_start();
	require_once 'dbinteraction.php';
	require_once 'fieldsbdd.php';


	$_SESSION['mail'] =$_GET['email'] ;

	$email = $_GET['email'];
	 ?>
	 <script>
	    function check_pass(){
	    if (document.getElementById('mdp1').value==document.getElementById('mdp2').value){
	 document.getElementById('submit').disabled = false
	  document.getElementById('message').style.color="green";
	 document.getElementById('message').innerHTML="correct";
	}
	else {
	 document.getElementById('submit').disabled = true;
	 document.getElementById('message').style.color="red";
	 document.getElementById('message').innerHTML="incorrect";


	}
	    }
	 </script>
	<html lang="fr">

	 <?php
	 include('header.html');
	;
	$cle = $_GET['cle'];
	if($cle==dbinteraction\get_cle_email($email))
	{?>
	<div style="padding-top:5px;display:inline-block;border:1px double black;background-color:#3B90AF;color:white;margin-top:20px;">
	<FORM method="post" action="registernewpassword.php">
	<div style="font-size: 150%;width:100%;display:inline-block;border-bottom:1px double black;">Modification du mot de passe</div>
	<div style="padding-top:20px;background-color:white;padding-bottom:20px;color:black;">
	<TABLE BORDER=0 ><TR> <TD>Nouveau mot de passe</TD>

	<TD> <INPUT type="password" name="mdp1" id="mdp1"  class="required"></INPUT>
	</TD></TR>
	<TR> <TD>Confirmation du mot de passe</TD>
	<TD><INPUT type="password" name="mdp2" id="mdp2" onkeyup='check_pass();'  class="required"> </INPUT><span id='message'></span></TD>

	</TR>
	</TABLE>
	<div style="margin-top:20px;font-size:125%;">
	<INPUT type="submit" value="Modifier mon mot de passe" id="submit" disabled> </INPUT>
	</div>
	</div>
	</FORM>

	<?php
	}
	?>

	</div>
	</div>
	</div>


	 
	</html>