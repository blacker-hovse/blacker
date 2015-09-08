			<p class="pull-right">Rows: <?
echo $r_count;
?>; Queries: <?
echo $q_count;
?>.</p>
			<p class="pull-left">Total Quotes: <?
echo $t;
?>; Pending Quotes <?
echo $p;
?>.</p>
		</div>
<?
print_footer(
	'Powered by <a href="http://www.qdbs.org/">QdbS</a> ' . $version,
	'A service of Blacker House'
);
?>	</body>
</html>
