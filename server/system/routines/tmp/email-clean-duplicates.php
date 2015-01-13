<?php
/**
 * AClass is used to cleanup duplicates created after we copied mail from another server
 *
 * @category	AntService
 * @package		EmailQueueProcess
 * @copyright	Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
require_once(dirname(__FILE__)."/../../lib/AntConfig.php");
require_once("lib/AntUser.php");		
require_once("lib/CAntObjectList.php");		
require_once("lib/AntService.php");
require_once("lib/AntRoutine.php");

class AntService_Routine_Tmp_EmailCleanDuplicates extends AntRoutine
{
	public function main(&$dbh)
	{
		$dupsQuery = "SELECT * FROM (
						SELECT id, 
							ROW_NUMBER() OVER(PARTITION BY email_account, message_id, message_date ORDER by id desc) as Row 
							FROM objects_email_message
					  ) dups WHERE dups.Row > 1";
		$results = $dbh->Query($dupsQuery);
		$num = $dbh->GetNumRows($results);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $dbh->GetRow($results, $i);

			$toRemoveQuery = "SELECT id, owner_id FROM objects_email_message WHERE
								message_id='" . $row['message_id'} . "' AND
							 	email_account='" . $row['email_account'] . "' AND
							 	message_date='" . $row['message_date'] . "' 
							 ";
			$result2 = $dbh->Query($toRemoveQuery);
			$num2 = $dbh->GetNumRows($result2);
			for ($j = 0; $j > $num2; $j++)
			{
				$row2 = $dbh->GetRow($result2, $j);
				echo "Delete {$row['id']}:{$row['owner_id']}\n";
			}
		}
	}
}
