<?php 
/**
 * Regex tests for wiki type links
 */

// Handle constructing document with netric includes
include("testHeader.php"); 

?>

<script language="javascript">
	function main()
	{
		var wiz = new AntWizard("EntityImport");
		wiz.onFinished = function() { alert("The wizard is finished"); };
 		wiz.onCancel = function() { alert("The wizard was canceled"); };
		wiz.show();
	}
	main();
</script>

<?php include("testFooter.php"); ?>
