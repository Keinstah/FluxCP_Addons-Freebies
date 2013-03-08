<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('ListFreebiesHeading')) ?></h2>
<table class='horizontal-table'>
<tr>
	<th class='tooltip' title='Code'>Code</th>
	<th class='tooltip' title='Items'>Item</th>
	<th class='tooltip' title='Zeny'>Zeny</th>
	<th class='tooltip' title='Credits'>Credits</th>
	<th class='tooltip' title='Expiration'>Expire</th>
	<th class='tooltip' title='Used Limit'>Usage Limit</th>
	<th class='tooltip' title='User For'>User For</th>
	<th class='tooltip' title='User For IDs'>User For IDs</th>
	<th class='tooltip' title='Restrict IP Address'>Restrict IP</th>
</tr>
<?php foreach ($code_list as $row): ?>
<tr title='The code has been used <?php $usageCount = counterUsage($row->id, $server); echo $usageCount; echo ($usageCount > 1 ? " times" : " time") ?>.'>
	<td><a class='tooltip' href='<?php echo $this->url('freebies', 'edit'); echo (Flux::config("UseCleanUrls")) ? "?id=".$row->id : "&id=".$row->id ?>'><?php echo htmlentities($row->code) ?></a></td>
	<td><?php echo ($row->items == "" ? "<span style='color:gray'>Disabled</span>" : htmlentities($row->items)) ?></td>
	<td><?php echo htmlentities($row->zeny) ?></td>
	<td><?php echo htmlentities($row->credits) ?></td>
	<td class='tooltip' title='The code will expire on <?php echo date("F d, Y", $row->expiration) ?>'><?php echo date(Flux::config("DateFormat"), $row->expiration) ?></td>
	<td><?php echo (!$row->used_limit) ? "<span class='tooltip' title='Unlimited usage until it expired'>Unli</span>" : "<span class='tooltip' title='".($row->used_limit-getUsage($row->id, $server))." Usage left'>".getUsage($row->id, $server)." / ".$row->used_limit; ?></td>
	<td><?php echo (is_null($row->to) ? "<span style='color:gray'>Disabled</span>" : htmlentities($row->to)) ?></td>
	<td><?php echo (is_null($row->to_id) ? "<span style='color:gray'>Disabled</span>" : htmlentities($row->to_id)) ?></td>
	<td><?php echo ($row->restrict_ip_address) ? "Yes" : "No" ?></td>
</tr>
<tr>
	<td colspan='9' style='background:#b4c9ff;color:#333'>Description: <?php echo htmlentities($row->description) ?></td>
</tr>
<?php endforeach; ?>
</table>