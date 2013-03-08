<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
include("function.php");
$error = NULL;
$success = NULL;

if (isset($_POST['del']) && isset($_POST['id']))
{
	$id = (int) $params->get('id');
	$freebies_code_table = Flux::config('FluxTables.freebies_code');

	$sql = "DELETE FROM `{$server->loginDatabase}`.`{$freebies_code_table}` WHERE `id` = ?";
	$sth = $server->connection->getStatement($sql);
	if ($sth->execute(array($id)))
	{
		$error = Flux::message('FreebiesDeleted');
		$this->redirect($this->url('freebies', 'list'));
	} else
	{
		$error = Flux::message('UnexpectedError');
	}
}

if (count($_POST) && !isset($_POST['del']))
{
	$code		 = $params->get('code');
	$items		 = $params->get('items');
	$zeny		 = (int) $params->get('zeny');
	$credits	 = (int) $params->get('credits');
	$expire		 = $params->get('expiration');
	$used_limit  = (int) $params->get('used_limit');
	$restrict_ip = (int) $params->get('restrict_ip');
	$id			 = (int) $params->get('id');
	$description = $params->get('desc');
	$to			 = $params->get('to');
	$to_id		 = $params->get('to_id');
	$freebies_code_table = Flux::config('FluxTables.freebies_code');
	$updated	 = 0 ;

	if (isChanged($id, "code", $code, $server))
	{

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
		{
			changeValue($id, "code", $code, $server);
			$updated = 1;
		}
	}

	if (isChanged($id, "items", $items, $server))
	{
		if ($items != "" && !preg_match(Flux::config('itemChars'), $items))
		{
			$error = Flux::message("InvalidItemsFormat");
		} else
		{
			changeValue($id, "items", $items, $server);
			$updated = 1;
		}
	}

	if (isChanged($id, "zeny", $zeny, $server))
	{
		if ($zeny < 0 && $zeny > 999999999)
		{
			$error = Flux::message("InvalidZeny");
		} else
		{
			changeValue($id, "zeny", $zeny, $server);
			$updated = 1;
		}
	}

	if (isChanged($id, "credits", $credits, $server))
	{
		if ($credits < 0 && $credits > 999999999)
		{
			$error = Flux::message("InvalidCredit");
		} else
		{
			changeValue($id, "credits", $credits, $server);
			$updated = 1;
		}
	}
	
	if (isChanged($id, "used_limit", $used_limit, $server))
	{
		if ($riftpoints < 0 && $used_limit > 999999999)
		{
			$error = Flux::message("InvalidUsedLimit");
		} else
		{
			changeValue($id, "used_limit", $used_limit, $server);
			$updated = 1;
		}
	}
	
	if (isChanged($id, "expiration", strtotime($expire), $server))
	{
		if (!validate_date($expire))
		{
			$error = Flux::message("InvalidExpirationFormat");
		} else
		{
			changeValue($id, "expiration", strtotime($expire), $server);
			$updated = 1;
		}
	}
	
	if (isChanged($id, "restrict_ip_address", $restrict_ip, $server))
	{
		changeValue($id, "restrict_ip_address", $restrict_ip, $server);
		$updated = 1;
	}
	
	if (isChanged($id, "description", $description, $server))
	{
		if (strlen($description) > 55)
		{
			$error = Flux::message("InvalidDescriptionLength");
		} else
		{
			changeValue($id, "description", $description, $server);
			$updated = 1;
		}
	}

	
	if (isChanged($id, "to", $to, $server) || isChanged($id, "to_id", $to_id, $server))
	{
		$to_error = NULL;

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
					$to_error = 1;
				}
			}
		}
		
		if (is_null($to_error))
		{
			changeValue($id, "to", $to, $server);
			changeValue($id, "to_id", $to_id, $server);
			$updated = 1;
		}
	}

	if (!$updated)
	{
		if (is_null($error)) $error = Flux::message("NoFieldUpdates");
	} else
	{
		$success = Flux::message("FieldUpdates");
	}
}

if (isset($_GET['id']))
{
	$id = (int) $_GET['id'];
	$freebies_code_table = Flux::config('FluxTables.freebies_code');
	$res = NULL;

	$sql = "SELECT * FROM `{$server->loginDatabase}`.`{$freebies_code_table}` WHERE `id` = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($id));

	if ($sth->rowCount() > 0) $res = $sth->fetch();
}
else
{
	$res = NULL;
}
?>