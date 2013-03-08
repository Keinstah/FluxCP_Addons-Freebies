<?php if (!defined('FLUX_ROOT')) exit;
include("function.php");

$this->loginRequired();
$sql = "SELECT `name`, `char_id` FROM `{$server->loginDatabase}`.`char` WHERE `account_id` = ? ORDER BY `char_num` ASC";
$sth = $server->connection->getStatement($sql);
$sth->execute(array($session->account->account_id));

$freebies_code_table = Flux::config('FluxTables.freebies_code');
$freebies_logs_table = Flux::config('FluxTables.freebies_logs');

if ($sth->rowCount() > 0)
{
	$chars = $sth->fetchAll();
} else
{
	$chars = NULL;
}

$sql = "SELECT `claim_timestamp`, `code` FROM `{$server->loginDatabase}`.`{$freebies_logs_table}` WHERE `account_id` = ?";
$sth = $server->connection->getStatement($sql);
$sth->execute(array((int)$session->account->account_id));

$claimed_freebies = $sth->fetchAll();
$error = NULL;
$success = NULL;

if (count($_POST))
{
	$code		 = $params->get('code');
	$char_id	 = (int) $params->get('char_id');
	$ip_address = $_SERVER['REMOTE_ADDR'];
	$account_id = (int) $session->account->account_id;
	
	$sql = "SELECT * FROM `{$server->loginDatabase}`.`{$freebies_code_table}` WHERE `code` = ? AND `expiration` > ".time();
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($code));

	$res = $sth->fetch();
	$id = (int) $res->id;
	$used_limit = (int) $res->used_limit;
	$zeny = (int) $res->zeny;
	$credits = (int) $res->credits;
	$items = $res->items;
	$restrict_ip = $res->restrict_ip_address;

	if (isCharOnline($char_id, $server))
	{
		$error = Flux::message("CharIsOnline");
	} else
	if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]) || !empty($_SERVER['HTTP_CLIENT_IP']) || !empty($_SERVER['HTTP_X_FORWARDED']))
	{
		$error = Flux::message("ProxyIP");
	} else
	if ($char_id < 150000)
	{
		$error = Flux::message("UnexpectedError");
	} else
	if ($sth->rowCount() < 1)
	{
		$error = Flux::message("CodeExpiredOrDoesntExists");
	} else
	{
		$sql = "SELECT * FROM `{$server->loginDatabase}`.`{$freebies_logs_table}` WHERE `code_id` = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($id));

		if ($used_limit != 0 && $sth->rowCount() >= $used_limit)
		{
			$error = Flux::message("CodeReachedLimit");
		}
		else
		{
			$ready = 1;
			if ($restrict_ip)
			{
				$sql = "SELECT `id` FROM `{$server->loginDatabase}`.`{$freebies_logs_table}` WHERE `code_id` = ? AND `ip_address` != ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($id, $ip_address));

				if ($sth->rowCount() > 0)
				{
					$error = Flux::message("AlreadyClaimed");
				}
			}

			if (!is_null($res->to))
			{
				$to_id = explode(":", $res->to_id);
				$to = ($res->to == "account" ? $account_id : $char_id);

				foreach ($to_id as $row)
				{
					if (preg_match("/to/", $row))
					{
						$from_to = explode('to', $row);
						if ($to < $from_to[0])
						{
							$error = Flux::message("CodeExpiredOrDoesntExists");
							break;
						} else
						if ($to > $from_to[1])
						{
							$error = Flux::message("CodeExpiredOrDoesntExists");
							break;
						}

					} else
					if ($to != $row)
					{
						$error = Flux::message("CodeExpiredOrDoesntExists");
						break;
					}
				}
			}

			if (is_null($error))
			{
				$sql = "SELECT `id` FROM `{$server->loginDatabase}`.`{$freebies_logs_table}` WHERE `code_id` = ? AND `account_id` = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($id, $account_id));

				if ($sth->rowCount() > 0)
				{
					$error = Flux::message("AlreadyClaimed");
				}
				else
				{
					$sql = "INSERT INTO `{$server->loginDatabase}`.`{$freebies_logs_table}` ";
					$sql .= "(`code`, `code_id`, `account_id`, `ip_address`) VALUES (?, ?, ?, ?)";
					$sth = $server->connection->getStatement($sql);

					if ($sth->execute(array($code, $id, $account_id, $ip_address)))
					{
						// Claim Zeny
						if ($zeny != 0)
						{
							$sql = "SELECT `zeny` FROM `{$server->loginDatabase}`.`char` WHERE `char_id` = ?";
							$sth = $server->connection->getStatement($sql);
							$sth->execute(array($char_id));

							// Zeny Cap
							$myZeny = $sth->fetch()->zeny;
							if ($myZeny < 1000000000)
							{
								if (($myZeny+$zeny) > 1000000000)
								{
									$zeny = (1000000000-$myZeny);
								}
								$sql = "UPDATE `{$server->loginDatabase}`.`char` SET `zeny` = `zeny` + ? WHERE `char_id` = ?";
								$sth = $server->connection->getStatement($sql);
								$sth->execute(array($zeny, $char_id));
							}
						}

						// Claim Credits
						if ($credits != 0 && $creidts < 999999999)
						{
							$session->loginServer->depositCredits((int)$account_id, (int)$credits);
						}

						// Claim Items
						if ($items != "")
						{
							$types = array(4, 5, 7, 8);

							if (preg_match("/[\,\:]/", $items))
							{
								$item_id = explode(",", $items);
								foreach ($item_id as $row)
								{
									$item = explode(":", $row);
									if (!array_key_exists(1, $item))
									{
										$item[1] = 1;
									}
									$id = (int)$item[0];
									$amount = (int)$item[1];
									$type = getItemType($id, $server);

									if (!in_array($type, $types))
									{
										$sql = "INSERT INTO `{$server->loginDatabase}`.`inventory` ";
										$sql .= "(`char_id`, `nameid`, `amount`, `identify`) VALUES ";
										$sql .= "(?, ?, ?, 1)";
										$sth = $server->connection->getStatement($sql);
										$sth->execute(array($char_id, $id, $amount));
									} else
									{
										for ($i = 0; $i < $amount; $i++)
										{
											$sql = "INSERT INTO `{$server->loginDatabase}`.`inventory` ";
											$sql .= "(`char_id`, `nameid`, `amount`, `identify`) VALUES ";
											$sql .= "(?, ?, 1, 1)";
											$sth = $server->connection->getStatement($sql);
											$sth->execute(array($char_id, $id));
										}
									}

								}
							}
							else
							{
								$sql = "INSERT INTO `{$server->loginDatabase}`.`inventory` ";
								$sql .= "(`char_id`, `nameid`, `amount`, `identify`, `equip`, `refine`, `attribute`, `card0`, `card1`, `card2`, `card3`, `expire_time`, `favorite`, `unique_id`) VALUES ";
								$sql .= "(?, ?, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
								$sth = $server->connection->getStatement($sql);
								$sth->execute(array($char_id, $items));
							}
						}
						
						$success = Flux::message("ClaimSuccess");
					} else
					{
						$error = Flux::message("UnexpectedError");
					}
				}
			}
		}
		
	}
}

?>