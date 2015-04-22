<?php
	function getRegisterForm() {
		echo '
		<form id="register" action="register.php" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">Registration</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td>Username:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="name" id="name" maxlength="50" style="width: 100%" value="" /></td>
									</tr>
									<tr>
										<td><span class="smalltext">Password:</span></td>
										<td><span class="smalltext">Confirm Password:</span></td>
									</tr>
									<tr>
										<td><input type="password" name="password" id="password" maxlength="50" style="width: 100%" /></td>
										<td><input type="password" name="password2" id="password2" maxlength="50" style="width: 100%" /></td>
									</tr>
									<tr>
										<td><span class="smalltext"><label for="email">Email:</label></span></td>
										<td><span class="smalltext"><label for="email2">Confirm Email:</label></span></td>
									</tr>
									<tr>
										<td><input type="text" name="email" id="email" maxlength="50" style="width: 100%" value="" /></td>
										<td><input type="text" name="email2" id="email2" maxlength="50" style="width: 100%" value="" /></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr>
			</tbody>
			</table>
			<br />
			<div id="submit" align="center">
				<input type="submit" name="Submit" value="Submit Registration!" />
			</div>
		</form>';
	}
	print_r($_POST);
?>