<?php 
/**
 * Regex tests for wiki type links
 */

// Handle constructing document with netric includes
include("testHeader.php"); 
$USER->setSetting("help/tours/tests/1-first/dismissed", '0');
$USER->setSetting("help/tours/tests/2-second/dismissed", '0');
$USER->setSetting("help/tours/tests/3-inline/dismissed", '0');
$USER->setSetting("help/tours/tests/4-dialog/dismissed", '0');

?>

<div id='second' data-tour='tests/2-second'>Second Item: Backwords</div>

<br /><br /><br /><br />

<div id='first' data-tour='tests/1-first'>Firs Item: To test loading order</div>

<br /><br /><br /><br />

<div id='second-rpt' data-tour='tests/2-second'>Second Item Repeat: should not load again</div>

<br /><br /><br /><br />

<div id='second-rpt' data-tour='tests/3-inline' data-tour-type='inline'>Inline Will Load Here</div>

<br /><br /><br /><br />

<div id='second-rpt' data-tour='tests/4-dialog' data-tour-type='dialog'></div>

<div>Plain text</div>

<div id='debug'></div>

<script language="javascript">
	function main()
	{
		Ant.HelpTour.loadTours(document.body);
	}
	main();
</script>

<?php include("testFooter.php"); ?>
