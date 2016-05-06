<phpunit>
	<testsuites>
		<testsuite name="Team Curiosity Test">
			<file>UserTest.php</file>
			<file>CommentImageTest.php</file>
			<file>favoriteNewsArticleTestTest.php</file>
		</testsuite>
	</testsuites>
	<filter>
		<whitelist processUncoveredFilesFromWhitelist="true">
			<test suffix=".php">../php/classes</test>
		</whitelist>
	</filter>
</phpunit>