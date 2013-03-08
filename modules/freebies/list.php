<?php if (!defined('FLUX_ROOT')) exit;
include("function.php");

$freebies_code_table = Flux::config('FluxTables.freebies_code');

$sql = "SELECT `id`, `code`, `timestamp`, `expiration`, `credits`, `items`, `zeny`, `used_limit`, `description`, `restrict_ip_address`,`to`, `to_id` FROM `{$server->loginDatabase}`.`{$freebies_code_table}` ORDER BY `timestamp` DESC";
$sth = $server->connection->getStatement($sql);
$sth->execute();
$code_list = $sth->fetchAll();

?>