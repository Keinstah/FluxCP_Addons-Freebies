<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
include("function.php");
$error = NULL;
$success = NULL;

if (count($_POST))
{
	$code		 = $params->get('code');
	$items		 = $params->get('items');
	$zeny		 = (int) $params->get('zeny');
	$credits	 = (int) $params->get('credits');
	$expire		 = $params->get('expiration');
	$used_limit  = (int) $params->get('used_limit');
	$restrict_ip = (int) $params->get('restrict_ip');
	$description = $params->get('desc');
	$to			 = $params->get('to');
	$to_id		 = $params->get('to_id');
	$freebies_code_table = Flux::config('FluxTables.freebies_code');

	$sql = "SELECT `code` FROM `{$server->loginDatabase}`.`{$freebies_code_table}` WHERE `code` = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($code));

	if ($code == "")
	{
		$error = Flux::message("CodeFieldRequired");
	} else
	if (strlen($code) < Flux::config('codeMin') || strlen($code) > Flux::config('codeMax'))
	{
		$error = sprintf(Flux::message("CodeLength"), Flux::config('codeMin'), Flux::config('codeMax'));
	} else
	if (!preg_match(Flux::config('codeChars'), $code))
	{
		$error = Flux::message("CodeAlphaNumeric");
	} else
	if ($sth->rowCount() > 0)
	{
		$error = Flux::message("CodeAlreadyExists");
	} else
	if ($items != "" && !preg_match(Flux::config('itemChars'), $items))
	{
		$error = Flux::message("InvalidItemsFormat");
	} else
	if ($zeny < 0 && $zeny > 999999999)
	{
		$error = Flux::message("InvalidZeny");
	} else
	if ($credits < 0 && $credits > 999999999)
	{
		$error = Flux::message("InvalidCredit");
	} else
	if (!validate_date($expire))
	{
		$error = Flux::message("InvalidExpirationFormat");
	} else
	if (strlen($description) > 55)
	{
		$error = Flux::message("InvalidDescriptionLength");
	} else
	if ($to != 'disable' && $to != 'account' && $to != 'char')
	{
		$error = Flux::message("UnexpectedError");
	} else
	if ($to != 'disable' && $to_id == '')
	{
		$error = Flux::message("UseForRequired");
	} else
	if ($to != 'disable' && !preg_match(Flux::config('toIDChars'), $to_id))
	{
		$error = Flux::message("InvalidUseForFormat");
	} else
	{
		if ($to == 'disable')
		{
			$to = NULL;
			$to_id = NULL;
		} else {
			$ids = explode(":", $to_id);
			$i = 0;
			foreach ($ids as $row)
			{	
				if (preg_match("/to/", $row))
				{
					unset($ids[$i]); 
					$from_to = explode("to", $row);

					if ($from_to[0] > $from_to[1])
					{
						$error = Flux::message("InvalidUserForFromTo");
						break;
					}

					$ids = array_merge_recursive($ids, $from_to);
				}
				$i++;
			}

			if ($error == "")
			{
				$table = ($to == 'account' ? "login" : "char");
				$sql = sprintf("SELECT %s_id FROM $server->loginDatabase.%s WHERE ", $to, $table);

				for ($i = 0; $i < count($ids); $i++)
				{
					if ($i != 0)
					{
						$sql .= sprintf(" OR %s_id = ?", $to);
					} else {
						$sql .= sprintf("%s_id = ?", $to);
					}
				}

				$sth = $server->connection->getStatement($sql);
				$sth->execute($ids);
				
				if ($sth->rowCount() != count($ids))
				{
					$error = Flux::message("InvalidUserForID");
				}
			}
		}

		if ($error == "")
		{
			$expire = strtotime($expire);
			$sql = "INSERT INTO `{$server->loginDatabase}`.`{$freebies_code_table}` ";
			$sql .= "(`code`, `expiration`, `used_limit`, `items`, `zeny`, `credits`, `description`, `restrict_ip_address`, `to`, `to_id`)";
			$sql .= " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$sth = $server->connection->getStatement($sql);
			$bind = array($code, $expire, $used_limit, $items, $zeny, $credits, $description, $restrict_ip, $to, $to_id);
			
			if ($sth->execute($bind))
			{
				$success = Flux::message("CodeInserted");
			}
			else
			{
				$error = Flux::message("UnexpectedError");
			}
		}
	}
}

?>