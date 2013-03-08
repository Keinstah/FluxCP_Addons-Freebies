<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('FreebiesHeading')) ?></h2>

<?php echo (!is_null($error) ? "<div class='red'>{$error}</div>" : 
			(!is_null($success) ? "<div class='green'>{$success}</div>" : "")) ?>
<?php if (is_null($chars)): ?>
<div class='message'><?php echo htmlspecialchars(Flux::message('FreebiesError'))  ?></div>
<?php else: ?>
<form action="<?php echo $this->url('freebies') ?>" method="post">
	<h3><?php echo htmlspecialchars(Flux::message('FreebiesHeading2')) ?></h3>
	<table class='vertical-table'>
		<tr>
			<td><label for='char_id'>Character:</td>
			<td><select id='char_id' class='tooltip' title='<?php echo htmlspecialchars(Flux::message('FreebiesInfo')) ?>' name='char_id'>
				<?php foreach($chars as $row): ?>
				<option value='<?php echo $row->char_id ?>'><?php echo htmlentities($row->name); ?></option>
				<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td><label for='freebies_code'>Code:</td>
			<td><input type='text' id='freebies_code' name='code' value='<?php echo htmlentities($params->get('code')); ?>' /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' value='Redeem Freebies' onclick="if(!confirm('Are you sure your character is offline?')) return false;" /></td>
		</tr>
	</table>
	</form>
	<?php endif; ?>
	<h3><?php echo htmlspecialchars(Flux::message('FreebiesHeading3')) ?></h3>
	<?php if (count($claimed_freebies) > 0): ?>
	<table class='horizontal-table'>
		<tr>
			<th>Code</th>
			<th>Claimed Date Time</th>
		</tr>
		<?php foreach ($claimed_freebies as $row): ?>
		<tr>
			<td><?php echo $row->code; ?></td>
			<td><?php echo $row->claim_timestamp; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php else: ?>
	<span><?php echo htmlspecialchars(Flux::message('FreebiesInfo2')) ?></span>
	<?php endif; ?>