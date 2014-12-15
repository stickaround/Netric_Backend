<?php 
/**
 * Regex tests for wiki type links
 */

// Handle constructing document with netric includes
include("testHeader.php"); 

?>

<?php
	// Test [[link|title]]
	echo preg_replace( '#\\[\\[([^|\\]]*)?\\|(.*?)\\]\\]#s', '1 = $1, 2 = $2', "[[my-link|My Title]]");
	echo "<br /><br />";
	// Test [[link]]
	echo preg_replace( '#\\[\\[(.*?)\\]\\]#s', '1 = $1', "[[my-link]]");
	echo "<br /><br />";

	// Now work with JS
	echo "<div>Below is JS output</div>";
	echo "<div id='jscon'></div>";
?>

<script language="javascript" type="text/javascript">
	var g_userid = "<?php print($USERID); ?>";
    
	function main()
	{
		var con = document.getElementById("jscon");

		// Test [[link]]
		var str = "[[my-link|My Title]]";
		//var re=/\[\[(.*?)\|(.*?)\]\]/gi
		var re=/\[\[([^|\]]*)?\|(.*?)\]\]/gi
		con.innerHTML += str.replace(re, "1 = $1, 2 = $2");

		con.innerHTML += "<br /><br />";

		// Test [[link]]
		var str = "[[my-link]]";
		var re=/\[\[(.*?)]\]/gi
		con.innerHTML += str.replace(re, "1 = $1");
	}
	
	main();
</script>

<?php include("testFooter.php"); ?>
