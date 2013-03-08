<?php if (!defined('FLUX_ROOT')) exit;
return array(
	'codeChars'		=> "/[a-zA-Z0-9]/",
	'codeMin'		=> 6,
	'codeMax'		=> 25,
	'itemChars'		=> "/[0-9\,\:]/",
	'toIDChars'		=> "/[0-9\:\to]/",			
	'MenuItems'		=> array(
		'Other'		=> array(
			'Freebies' => array(
				'module' => 'freebies')
			)
		),
	'SubMenuItems'	=> array(
		'freebies'	=> array(
			'index'=> 'Redeem',
			'list'	=> 'List',
			'add'	=> 'Add',
			//'edit'	=> 'Edit',
			//'del'	=> 'Delete',
			)
		),
	'FluxTables'	=> array(
		'freebies_logs' => 'cp_freebies_logs',
		'freebies_code' => 'cp_freebies_code',
		)
)
?>