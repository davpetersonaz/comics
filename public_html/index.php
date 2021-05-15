<?php include_once('../router.php'); ?>
<?php include(HTMLS_PATH.'templates/header.php'); ?>

		<div class="container-fluid">
			<!--  CONTENT BELOW  -->

<?php include(HTMLS_PATH.$page.'.php'); ?>

			<!--  CONTENT COMPLETE  -->
		</div><!-- END-container-fluid -->

<?php include(HTMLS_PATH.'templates/footer.php'); ?>

	</body>
</html>
