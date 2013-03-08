<?php if (!defined('FLUX_ROOT')) exit;

function validate_date($date) {
	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date))
	{
		list($year, $month, $day) = explode('-', $date);
		return checkdate($month, $day, $year);
	}
	else
	{
		return 0;
	}
} 

function getItemType($item_id, $server)
{
	$sql = "(SELECT type FROM $server->loginDatabase.item_db_re WHERE id = ?) UNION (SELECT type FROM $server->loginDatabase.item_db2 WHERE id = ?)";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array((int)$item_id, (int)$item_id));

	if ($sth->rowCount())
	{
		$res = $sth->fetch();
		return $res->type;
	}
	else
	{
		return FALSE;
	}
}

function isCharOnline($char_id, $server)
{
	$sql = "SELECT `online` FROM `{$server->loginDatabase}`.`char` WHERE `char_id` = ?";
	$sth = $server->connection->getStatement($sql);
	$bind = array((int)$char_id);

	$sth->execute($bind);

	if ($sth->fetch()->online == 1)
	{
		return 1;
	} else
	{
		return 0;
	}
}

function getUsage($id, $server)
{
	$freebies_logs_table = Flux::config('FluxTables.freebies_logs');
	$sql = "SELECT `id` FROM `{$server->loginDatabase}`.`{$freebies_logs_table}` WHERE `code_id` = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array((int)$id));

	return $sth->rowCount();
}

function isChanged($id, $col, $row, $server)
{

	$freebies_code_table = Flux::config('FluxTables.freebies_code');

	$sql = "SELECT `id` FROM `{$server->loginDatabase}`.`{$freebies_code_table}` WHERE `id` = ? AND `{$col}` = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($id, $row));

	if ($sth->rowCount() > 0)
	{
		return 0;
	}
	else
	{
		return 1;
	}
}

function changeValue($id, $col, $row, $server)
{
	$freebies_code_table = Flux::config('FluxTables.freebies_code');

	if (is_array($col) && is_array($row) && count($col) == count($row))
	{
		$sql = "UPDATE `{$server->loginDatabase}`.`{$freebies_code_table}` SET";
		for ($i = 0; $i < count($col); $i++)
		{
			$sql .= " `{$col[$i]}` = ?";
			if ((count($col)-1) != $i)
			{
				$sql .= " AND";
			}
		}
		$sth = $server->connection->getStatement($sql);

		if ($sth->execute($row))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	else
	{
		$sql = "UPDATE `{$server->loginDatabase}`.`{$freebies_code_table}` SET `{$col}` = ? WHERE `id` = ?";
		$sth = $server->connection->getStatement($sql);

		if ($sth->execute(array($row, (int)$id)))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
}

function counterUsage($id, $server)
{
	$freebies_logs_table = Flux::config('FluxTables.freebies_logs');
	$sql = "SELECT * FROM $server->loginDatabase.$freebies_logs_table WHERE code_id = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array((int) $id));
	return $sth->rowCount();
}
 
?>