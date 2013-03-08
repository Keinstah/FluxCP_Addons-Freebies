<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('AddFreebiesHeading')) ?></h2>
<?php echo (!is_null($error) ? "<div class='red'>{$error}</div>" : 
			(!is_null($success) ? "<div class='green'>{$success}</div>" : "")) ?>
<form action="<?php echo $this->url('freebies', 'add') ?>" method="post">
<table class='generic-form-table'>
	<tr>
		<th><label for='freebies_code'>Code:</th>
		<td><input type='text' id='freebies_code' name='code' value='<?php echo htmlentities($params->get('code')); ?>' /></td>
	</tr>
	<tr>
		<th><label for='items'>Items:</th>
		<td><input type='text' id='items' name='items' value='<?php echo htmlentities($params->get('items')); ?>' /><span class='important'><?php echo htmlspecialchars(Flux::message('AddFreebiesInfo')) ?></span></td>
	</tr>
	<tr>
		<th><label for='zeny'>Zeny:</th>
		<td><input type='text' id='zeny' name='zeny' value='<?php echo (int)htmlentities($params->get('zeny')); ?>' /></td>
	</tr>
	<tr>
		<th><label for='credits'>Credits:</th>
		<td><input type='text' id='credits' name='credits' value='<?php echo (int)htmlentities($params->get('credits')); ?>' /></td>
	</tr>
	<tr>
		<th><label for='expiration'>Expiration:</th>
		<td><input type='text' id='expiration' name='expiration' value='<?php echo htmlentities($params->get('expiration')); ?>' /><span class='important'><?php echo htmlspecialchars(Flux::message('AddFreebiesInfo2')) ?></span></td>
	</tr>
	<tr>
		<th><label for='used_limit'>Used Limit:</th>
		<td><input type='text' id='used_limit' name='used_limit' value='<?php echo (int)htmlentities($params->get('used_limit')); ?>' /><span class='important'><?php echo htmlspecialchars(Flux::message('AddFreebiesInfo3')) ?></span></td>
	</tr>
	<tr>
		<th><label for='restrict_ip'>Restrict IP Address:</th>
		<td>
			<select id='restrict_ip' name='restrict_ip'>
				<option value='0'>No</option>
				<option value='1'>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for='to'>Use for Account ID or Char ID:</th>
		<td><select id='to' name='to'>
			<option value='disable'<?php echo ($params->get('to') == 'disable' ? " selected='selected'" : "") ?>>Disable</option>
			<option value='account'<?php echo ($params->get('to') == 'account' ? " selected='selected'" : "") ?>>Account</option>
			<option value='char'<?php echo ($params->get('to') == 'char' ? " selected='selected'" : "") ?>>Character</option>
		</select>
		<input type='text' name='to_id' value='<?php echo htmlentities($params->get('to_id')) ?>' />
		<span class='important' id='to_desc'>ID:ID2:ID3:ID4toID5(ID4 must not be higher than ID5)</span>
		</td>
	</tr>
	<tr>
		<th><label for='desc'>Description (optional):</th>
		<td>
			<input type='text' id='desc' name='desc' value='<?php echo htmlentities($params->get('desc')) ?>' />
		</td>
	</tr>
	<tr>
		<td></td>
		<td><input type='submit' value='Add Freebies Code' /></td>
	</tr>
</table>
</form>
<script type='text/javascript'>
$(function() {
	var to_id = $('[name=to_id], #to_desc');
	<?php if ($params->get('to') == '' || $params->get('to') == 'disable'): ?>
	to_id.hide();
	<?php endif ?>
	$('[name=to]').change(function() {
		if ($('[name=to]').val() == 'disable') {
			to_id.hide();
		} else {
			to_id.show();
		}
	});
});
</script>