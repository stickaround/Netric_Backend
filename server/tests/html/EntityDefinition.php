<?php 
/**
 * Regex tests for wiki type links
 */

// Handle constructing document with netric includes
include("testHeader.php"); 

?>
<div id='debug'></div>
<script language="javascript">
	function main()
	{
		var entDef = new Ant.EntityDefinition("customer");
		entDef.load(true);

		// Test loader (was manually printing from within the function to test)
		var entDef = Ant.EntityDefinitionLoader.get("task");
		var entDef2 = Ant.EntityDefinitionLoader.get("task");
	}
	
	main();
</script>

<?php include("testFooter.php"); ?>
